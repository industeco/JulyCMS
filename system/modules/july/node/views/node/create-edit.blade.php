@extends('layout')

@section('h1')
  {{ __('backend.'.$context['mode']) }}内容 <span id="content_locale">[ {{ $context['mold']->label }}({{ $context['mold']->id }}), {{ langname($langcode) }}({{ $langcode }}) ]</span>
@endsection

@section('main_content')
  <el-form id="main_form" ref="main_form"
    :model="model"
    :rules="rules"
    label-position="top">
    <div id="main_form_left">
      {{-- 标题字段 --}}
      <el-form-item prop="title" size="small" class="has-helptext" :rules="[{required:true, message:'标题不能为空', trigger:'blur'}]">
        <el-tooltip slot="label" content="title" placement="right" effect="dark" popper-class="jc-twig-output">
          <span>标题</span>
        </el-tooltip>
        <el-input v-model="model.title" native-size="100"></el-input>
        <span class="jc-form-item-help"><i class="el-icon-info"></i> 标题，可用作链接文字等。</span>
      </el-form-item>

      {{-- 自定义字段 --}}
      @foreach ($context['local_fields'] as $field)
      {!! $field->render($model[$field['id']] ?? null) !!}
      @endforeach

      {{-- 视图文件 --}}
      <el-form-item prop="view" size="small" class="has-helptext"
        :rules="[{pattern:/^(?:[a-z0-9\-_]+\/)*[a-z0-9\-_]+\.(?:twig|html?)$/, message:'格式不正确', trigger:'change'}]">
        <el-tooltip slot="label" content="view" placement="right" effect="dark" popper-class="jc-twig-output">
          <span>模板</span>
        </el-tooltip>
        <el-select v-model="model.view" filterable allow-create default-first-option style="width:100%;max-width:360px">
          @foreach ($context['views'] as $view)
          <el-option value="{{ $view }}"></el-option>
          @endforeach
        </el-select>
        <span class="jc-form-item-help"><i class="el-icon-info"></i> 指定模板</span>
      </el-form-item>

      {{-- 颜色属性 --}}
      <el-form-item size="small" label="红绿蓝">
        <el-tooltip popper-class="jc-twig-output" effect="dark" content="is_red" placement="top">
          <el-switch style="margin-right: 1em" v-model="model.is_red" active-color="#F44336" inactive-color="#FFCDD2"></el-switch>
        </el-tooltip>
        <el-tooltip popper-class="jc-twig-output" effect="dark" content="is_green" placement="top">
          <el-switch style="margin-right: 1em" v-model="model.is_green" active-color="#4caf50" inactive-color="#C8E6C9"></el-switch>
        </el-tooltip>
        <el-tooltip popper-class="jc-twig-output" effect="dark" content="is_blue" placement="top">
          <el-switch style="margin-right: 1em" v-model="model.is_blue" active-color="#2196F3" inactive-color="#BBDEFB"></el-switch>
        </el-tooltip>
      </el-form-item>

      {{-- 保存按钮 --}}
      <div id="main_form_bottom" class="is-button-item">
        <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click.stop="submit">
          <div class="md-button-content">保存</div>
        </button>
      </div>
    </div>
    <div id="main_form_right">
      <h2 class="jc-form-info-item">通用非必填项</h2>

      {{-- 右侧全局字段 --}}
      <el-collapse :value="expanded">
        @foreach ($context['global_fields']->groupBy('field_group') as $fieldGroup => $globalFields)
        <el-collapse-item name="{{ $fieldGroup }}" title="{{ $fieldGroup }}">
          @foreach ($globalFields as $field)
          {!! $field->render($model[$field['id']] ?? null) !!}
          @endforeach
        </el-collapse-item>
        @endforeach
      </el-collapse>
    </div>
  </el-form>
@endsection

@section('script')
<script>
  window.showMediasWindow = function() {
    let mediaWindow = null;

    return function showMediasWindow() {
      const screenWidth = window.screen.availWidth;
      const screenHeight = window.screen.availHeight;

      const width = screenWidth*.8;
      const height = screenHeight*.8 - 60;
      const left = screenWidth*.1;
      const top = screenHeight*.15;

      if (!mediaWindow || mediaWindow.closed) {
        mediaWindow = window.open(
          "{{ short_url('media.select') }}",
          'chooseMedia',
          `resizable,scrollbars,status,top=${top},left=${left},width=${width},height=${height}`
        );
      } else {
        mediaWindow.focus()
      }
    }
  }();

  function recieveMediaUrl(url) {
    app.recieveMediaUrl(url)
  }

  let app = new Vue({
    el: '#main_content',
    data() {
      return {
        model: @jjson($model),
        rules: {},
        expanded: @jjson($context['global_fields']->pluck('field_group')->unique()->values()->all()),
      };
    },

    created: function() {
      this.original_model = _.cloneDeep(this.model);
    },

    methods: {
      getChanged() {
        const changed = [];
        for (const key in this.model) {
          if (! _.isEqual(this.model[key], this.original_model[key])) {
            changed.push(key);
          }
        }
        return changed;
      },

      showMedias(field) {
        this.recieveMediaUrlFor = field;
        showMediasWindow();
      },

      recieveMediaUrl(url) {
        if (this.recieveMediaUrlFor) {
          this.model[this.recieveMediaUrlFor] = url;
        }
      },

      submit() {
        const loading = this.$loading({
          lock: true,
          text: '正在保存内容 ...',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        for (const key in this.model) {
          const editor = this.$refs['ckeditor_' + key];
          // console.log('ckeditor_' + key);
          // console.log(editor);
          if (editor && editor.instance && editor.instance.mode != 'wysiwyg') {
            editor.instance.setMode('wysiwyg');
          }
        }

        setTimeout(() => {
          this.$refs.main_form.validate().then(() => {
            const changed = this.getChanged();
            @if ($context['mode'] === 'edit')
              if (!changed.length) {
                window.location.href = "{{ short_url('nodes.index') }}";
                return;
              }
            @endif

            const model = _.cloneDeep(this.model);
            model.langcode = '{{ $langcode }}';
            model._changed = changed;

            @if ($context['mode'] !== 'create')
            const action = "{{ short_url('nodes.update', $model['id']) }}";
            @else
            const action = "{{ short_url('nodes.store') }}";
            @endif

            axios.{{ $context['mode'] !== 'create' ? 'put' : 'post' }}(action, model)
              .then((response) => {
                // console.log(response);
                window.location.href = "{{ short_url('nodes.index') }}";
              })
              .catch((error) => {
                loading.close();
                this.$message.error(error);
              });
          }).catch((error) => {
            // console.error(error);
            loading.close();
          });
        }, 100);
      },
    }
  })
</script>
@endsection
