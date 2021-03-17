@extends('layout')

@section('h1')
  {{ __('backend.'.$mode) }}内容 <span id="content_locale">[ {{ $node_type['label'] }}({{ $node_type['id'] }}), {{ lang($langcode)->getName() }}({{ $langcode }}) ]</span>
@endsection

@section('main_content')
  <el-form id="main_form" ref="main_form"
    :model="node"
    :rules="rules"
    label-position="top">
    <div id="main_form_left">
      @foreach ($fields as $field)
        @if ($field['preset_type'] !== 'global')
          {!! $field['element'] !!}
        @endif
      @endforeach
      <el-form-item size="small" label="红绿蓝">
        <el-tooltip popper-class="jc-twig-output" effect="dark" content="is_red" placement="top">
          <el-switch style="margin-right: 1em" v-model="node.is_red" active-color="#F44336" inactive-color="#FFCDD2"></el-switch>
        </el-tooltip>
        <el-tooltip popper-class="jc-twig-output" effect="dark" content="is_green" placement="top">
          <el-switch style="margin-right: 1em" v-model="node.is_green" active-color="#4caf50" inactive-color="#C8E6C9"></el-switch>
        </el-tooltip>
        <el-tooltip popper-class="jc-twig-output" effect="dark" content="is_blue" placement="top">
          <el-switch style="margin-right: 1em" v-model="node.is_blue" active-color="#2196F3" inactive-color="#BBDEFB"></el-switch>
        </el-tooltip>
      </el-form-item>
      <div id="main_form_bottom" class="is-button-item">
        <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click="submitMainForm">
          <div class="md-button-content">保存</div>
        </button>
      </div>
    </div>
    <div id="main_form_right">
      <h2 class="jc-form-info-item">通用非必填项</h2>
      <el-collapse :value="expanded" @change="handleCollapseChange">
        {{-- <el-collapse-item name="taxonomy" id="node_tags_selector"
          title="{{ config('app.field_groups.taxonomy.label') }}">
          <el-form-item size="small" class="has-helptext">
            <el-select v-model="node.tags" placeholder="选择标签"
              multiple
              filterable
              allow-create
              default-first-option>
              <el-option
                v-for="tag in db.tags"
                :value="tag"
                :label="tag">
              </el-option>
            </el-select>
            <span class="jc-form-item-help"><i class="el-icon-info"></i> 新增时请留意当前语言版本</span>
          </el-form-item>
        </el-collapse-item> --}}
        <el-collapse-item name="page_present"
          title="{{ config('app.field_groups.page_present.label') }}">
          <el-form-item prop="url" size="small">
            <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" content="url" placement="right">
              <span>网址</span>
            </el-tooltip>
            <el-input
              v-model="node.url"
              placeholder="/index.html"
              native-size="100"
              maxlength="200"
              show-word-limit
              ></el-input>
          </el-form-item>
          <el-form-item prop="template" size="small" class="has-helptext">
            <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" content="template" placement="right">
              <span>模板</span>
            </el-tooltip>
            <el-autocomplete
              v-model="node.template"
              :fetch-suggestions="getTemplates"
              placeholder="home.twig"
              native-size="100"
              maxlength="200"
              show-word-limit></el-autocomplete>
              <span class="jc-form-item-help"><i class="el-icon-info"></i> twig 模板，用于渲染页面</span>
          </el-form-item>
        </el-collapse-item>
        <el-collapse-item name="page_meta"
          title="{{ config('app.field_groups.page_meta.label') }}">
          {!! $fields['meta_title']['element'] !!}
          {!! $fields['meta_keywords']['element'] !!}
          {!! $fields['meta_description']['element'] !!}
          {!! $fields['meta_canonical']['element'] !!}
        </el-collapse-item>
      </el-collapse>
    </div>
  </el-form>
@endsection

@section('script')

{{-- 通过 script:template 保存 html 内容 --}}
@foreach ($node as $key => $value)
@if (is_string($value) && strlen($value) > 255)
<script type="text/template" id="field__{{ $key }}">
  {!! $value !!}
</script>
@endif
@endforeach

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

      var isUniqueUrl = function(rule, value, callback) {
        if (!value || !value.length) {
          callback();
        } else {
          axios.post("{{ short_url('path_aliases.is_exist') }}", {
            langcode: '{{ $langcode }}',
            url: value,
            path: '{{ $node["path"] }}',
          }).then(function(response) {
            if (response.data.is_exist) {
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
        node: @json($node, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        rules: {
          url: [
            { pattern:/^(\/[a-z0-9\-_]+)+\.html$/, message:'网址格式不正确', trigger:'blur' },
            { validator:isUniqueUrl, trigger:'blur' },
          ],
          template: [
            { pattern: /^[a-z0-9\-_]+(\/[a-z0-9\-_]+)*\.twig$/, message: '模板格式不正确', trigger: 'blur' },
          ],
          @foreach ($fields as $field)
          @if($field['rules'])
          {{ $field['id'] }}: [
            @foreach ($field['rules'] as $rule)
            {!! $rule !!},
            @endforeach
          ],
          @endif
          @endforeach
          meta_canonical: [
            {type: 'url', message: '权威页面必须为完整网址', trigger: 'blur'},
          ],
        },

        db: {
          // {{-- tags: @json($context['tags'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE), --}}
          templates: @json($context['templates'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        },

        expanded: [],
      };
    },

    created: function() {
      this.initial_data = clone(this.node);
      const templates = [];
      this.db.templates.forEach(element => {
        templates.push({value: element});
      });
      this.db.templates = templates;

      @foreach(config('app.field_groups') as $group => $info)
      @if ($info['expanded'])
      this.expanded.push('{{ $group }}');
      @endif
      @endforeach
    },

    methods: {

      getTemplates(queryString, cb) {
        cb(this.db.templates);
      },

      handleCollapseChange(activeNames) {
        this.$set(this.$data, 'expanded', activeNames);
      },

      getChanged() {
        const changed = [];
        for (const key in this.node) {
          if (! isEqual(this.node[key], this.initial_data[key])) {
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
          this.node[this.recieveMediaUrlFor] = url;
        }
      },

      submitMainForm() {
        let form = this.$refs.main_form;

        const loading = this.$loading({
          lock: true,
          text: '正在保存内容 ...',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        for (const key in this.node) {
          if (this.$refs['ckeditor_'+key]) {
            const editor = this.$refs['ckeditor_'+key];
            if (editor.instance && editor.instance.mode !== 'wysiwyg') {
              editor.instance.setMode('wysiwyg');
            }
          }
        }

        form.validate().then(function() {
          const changed = app.getChanged();
          @if ($node['id'])
            if (!changed.length) {
              window.location.href = "{{ short_url('nodes.index') }}";
              return;
            }
          @endif

          const node = clone(app.node);
          node._changed = changed;
          // console.log(node)
          @if ($node['id'])
          const action = "{{ short_url('nodes.update', $node['id']) }}";
          @else
          const action = "{{ short_url('nodes.store') }}";
          @endif

          axios.{{ $node['id'] ? 'put' : 'post' }}(action, node)
            .then(function(response) {
              // console.log(response)
              // loading.close()
              window.location.href = "{{ short_url('nodes.index') }}";
            })
            .catch(function(error) {
              loading.close()
              app.$message.error(error);
            })
        }).catch(function(error) {
          console.error(error);
          loading.close();
        })
      },
    }
  })
</script>
@endsection
