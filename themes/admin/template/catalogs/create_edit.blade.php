@extends('admin::layout')

@section('h1')
  {{ $truename?'编辑目录':'新建目录' }} <span id="content_locale">[ {{ langname($interface_value_langcode) }} ]</span>
@endsection

@section('main_content')
  <el-form id="main_form" ref="main_form"
    :model="catalog"
    :rules="catalogRules"
    label-position="top">
    <div id="main_form_left">
      @include('admin::widgets.truename', ['readOnly' => $truename, 'scope' => 'catalog'])
      <el-form-item label="名称" prop="name" size="small">
        <el-input
          v-model="catalog.name"
          native-size="60"
          maxlength="32"
          show-word-limit></el-input>
      </el-form-item>
      <el-form-item label="描述" prop="description" size="small">
        <el-input
          type="textarea"
          rows="5"
          v-model="catalog.description"
          maxlength="200"
          show-word-limit></el-input>
      </el-form-item>
      <div id="main_form_bottom" class="is-button-item">
        <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click="submitMainForm">
          <div class="md-button-content">保存</div>
        </button>
      </div>
    </div>
    <div id="main_form_right">
      <h2 class="jc-form-info-item">通用非必填项</h2>
    </div>
  </el-form>
@endsection

@section('script')
<script>
  function clone(obj) {
    return JSON.parse(JSON.stringify(obj))
  }

  let mode = "{{ $truename ? 'edit' : 'create' }}";

  let app = new Vue({
    el: '#main_content',
    data() {

      var isUnique = function(rule, value, callback) {
        if (value && value.length) {
          const action = "{{ short_route('checkunique.catalogs', '#value#') }}";
          axios.get(action.replace('#value#', value)).then(function(response) {
            if (response.data.exists) {
              callback(new Error('『真名』已存在'))
            } else {
              callback()
            }
          }).catch(function(error) {
            console.error(error);
          })
        }
      };

      return {
        catalog: {
          interface_value_langcode: '{{ $interface_value_langcode }}',
          content_value_langcode: '{{ $content_value_langcode }}',
          truename: '{{ $truename ?? '' }}',
          name: '{{ $name ?? '' }}',
          description: '{{ $description ?? '' }}',
        },

        catalogRules: {
          @if (!$truename)
          truename: [
            { required: true, message: '『真名』不能为空', trigger: 'submit' },
            { max: 32, message: '最多 32 个字符', trigger: 'change' },
            { pattern: /^[a-z0-9_]+$/, message: '只能包含小写字母、数字和下划线', trigger: 'change' },
            { validator: isUnique, trigger: 'blur' }
          ],
          @endif
          name: [
            { required: true, message: '『名称』不能为空', trigger: 'submit' },
            { max: 32, message: '最多 32 个字符（或 16 个汉字）', trigger: 'change' }
          ],
          description: [
            { max: 200, message: '最多 200 个字符（或 100 个汉字）', trigger: 'change' }
          ],
        },
      }
    },

    @if($truename)
    created: function() {
      this.initial_data = JSON.stringify(this.catalog)
    },
    @endif

    methods: {
      submitMainForm() {
        let form = this.$refs.main_form;

        form.validate().then(function() {
          // form.$el.submit()
          const loading = app.$loading({
            lock: true,
            text: mode=='create'?'正在新建目录 ...':'正在保存修改 ...',
            background: 'rgba(255, 255, 255, 0.7)',
          });

          @if($truename)
            if (app.initial_data === JSON.stringify(app.catalog)) {
              window.location.href = "{{ short_route('catalogs.index') }}";
              return;
            }
          @endif

          @if($truename)
          const action = "{{ short_route('catalogs.update', $truename) }}";
          @else
          const action = "{{ short_route('catalogs.store') }}";
          @endif

          axios.{{ $truename ? 'put' : 'post' }}(action, app.catalog).then(function(response) {
            // loading.close()
            window.location.href = "{{ short_route('catalogs.index') }}";
          }).catch(function(error) {
            app.$message.error(error);
            // console.error(error)
            loading.close()
          })
        }).catch(function(error) {
          console.error(error);
        })
      },
    }
  })
</script>
@endsection
