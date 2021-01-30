@extends('layout')

@section('h1')
  {{ __('backend.'.$context['mode']) }}目录
@endsection

@section('main_content')
  <el-form id="main_form" ref="main_form"
    :model="model"
    :rules="rules"
    label-position="top">
    <div id="main_form_left">
      <x-handle :read-only="$context['mode']==='edit'" :unique-action="short_url('catalogs.exists', '_ID_')" />
      <x-label label="名称" />
      <x-description />
      <div id="main_form_bottom" class="is-button-item">
        <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click.stop="submit">
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
  let app = new Vue({
    el: '#main_content',
    data() {

      return {
        model: @jjson($model),
        rules: {},
      }
    },

    created() {
      this.original_model = _.cloneDeep(this.model)
    },

    methods: {
      submit() {
        let form = this.$refs.main_form;

        form.validate().then(() => {
          // form.$el.submit()
          const loading = this.$loading({
            lock: true,
            text: "{{ $context['mode']==='edit' ? '正在保存修改 ...' : '正在创建目录 ...' }}",
            background: 'rgba(255, 255, 255, 0.7)',
          });

          @if($context['mode']==='edit')
          if (_.isEqual(this.original_model, this.model)) {
            window.location.href = "{{ short_url('catalogs.index') }}";
            return;
          }
          const action = "{{ short_url('catalogs.update', $model['id']) }}";
          @else
          const action = "{{ short_url('catalogs.store') }}";
          @endif

          axios.{{ $context['mode']==='edit' ? 'put' : 'post' }}(action, this.model)
            .then((response) => {
              // loading.close()
              window.location.href = "{{ short_url('catalogs.index') }}";
            }).catch((error) => {
              this.$message.error(error);
              console.error(error);
              loading.close();
            });
        }).catch((error) => {
          // console.error(error);
        });
      },
    }
  })
</script>
@endsection
