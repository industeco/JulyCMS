@extends('layout')

@section('h1')
  {{ __('backend.'.$context['mode']) }}内容 <span id="content_locale">[ {{ $context['mold']->label }}({{ $context['mold']->id }}), {{ lang($model['langcode'])->getName() }}({{ $model['langcode'] }}) ]</span>
@endsection

@section('main_content')
  <el-form id="main_form" ref="main_form"
    :model="model"
    :rules="rules"
    label-position="top">
    <div id="main_form_left">
      @foreach ($context['local_fields'] as $field)
      {!! $field->render($model[$field['id']] ?? null) !!}
      @endforeach
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
      <div id="main_form_bottom" class="is-button-item">
        <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click.stop="submit">
          <div class="md-button-content">保存</div>
        </button>
      </div>
    </div>
    <div id="main_form_right">
      <h2 class="jc-form-info-item">通用非必填项</h2>
      <el-collapse :value="expanded">
        @foreach ($context['global_fields']->groupBy('group_title') as $groupTitle => $globalFields)
        <el-collapse-item name="{{ $groupTitle }}" title="{{ $groupTitle }}">
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

{{-- 通过 script:template 保存 html 内容 --}}
{{--
@foreach ($model as $key => $value)
@if (is_string($value) && strlen($value) > 255)
<script type="text/template" id="field__{{ $key }}">
  {!! $value !!}
</script>
@endif
@endforeach
 --}}

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

  function recieveFieldValue(id) {
    return document.getElementById('field__'+id).innerHTML;
  }

  let app = new Vue({
    el: '#main_content',
    data() {

      var isUniqueUrl = function(rule, value, callback) {
        if (!value || !value.length) {
          callback();
        } else {
          axios.post("{{ short_url('path_alias.exists') }}", {
            langcode: "{{ $model['langcode'] }}",
            url: value,
            path: '{{ $model["url"] }}',
          }).then(function(response) {
            if (response.data.exists) {
              callback(new Error('url 已存在'));
            } else {
              callback();
            }
          }).catch(function(error) {
            console.error(error);
          });
        }
      };

      return {
        model: @json($model, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        rules: {},
        expanded: @json($context['global_fields']->pluck('group_title')->unique()->values()->all(), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
      };
    },

    created: function() {
      this.initial_model = _.cloneDeep(this.model);
    },

    methods: {
      handleCollapseChange(activeNames) {
        // this.$set(this.$data, 'expanded', activeNames);
      },

      getChanged() {
        const changed = [];
        for (const key in this.model) {
          if (! _.isEqual(this.model[key], this.initial_model[key])) {
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

        for (const key in this.mode) {
          if (this.$refs['ckeditor_'+key]) {
            const editor = this.$refs['ckeditor_'+key];
            if (editor.instance && editor.instance.mode !== 'wysiwyg') {
              editor.instance.setMode('wysiwyg');
            }
          }
        }

        this.$refs.main_form.validate().then(() => {
          const changed = this.getChanged();
          @if ($context['mode'] === 'edit')
            if (!changed.length) {
              window.location.href = "{{ short_url('nodes.index') }}";
              return;
            }
          @endif

          const model = _.cloneDeep(this.model);
          model._changed = changed;

          @if ($context['mode'] !== 'create')
          const action = "{{ short_url('nodes.update', $model['id']) }}";
          @else
          const action = "{{ short_url('nodes.store') }}";
          @endif

          axios.{{ $context['mode'] !== 'create' ? 'put' : 'post' }}(action, model)
            .then((response) => {
              // console.log(response)
              window.location.href = "{{ short_url('nodes.index') }}";
            })
            .catch((error) => {
              loading.close()
              this.$message.error(error);
            });
        }).catch((error) => {
          // console.error(error);
          loading.close();
        });
      },
    }
  })
</script>
@endsection
