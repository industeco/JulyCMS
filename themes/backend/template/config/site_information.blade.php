@extends('backend::layout')

@section('h1', '网站基础设置')

@section('main_content')
<el-form id="main_form" ref="main_form"
  :model="configs"
  :rules="rules"
  label-position="top">
  <div id="main_form_left">
    @foreach ($configs as $key => $data)
    <el-form-item prop="{{ $key }}" size="small"
      class="{{ $data['description']?'has-helptext':'' }}">
      <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" :content="useInTwig('{{ $key }}')" placement="right">
        <span>{{ $data['label'] }}</span>
      </el-tooltip>
      <el-input
        v-model="configs['{{ $key }}']"
        native-size="80"></el-input>
        @if ($data['description'])
        <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $data['description'] }}</span>
        @endif
    </el-form-item>
    @endforeach
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
  const app = new Vue({
    el: '#main_content',
    data() {
      return {
        configs: {
          @foreach ($configs as $key => $data)
          "{{ $key }}": "{{ $data['value'] }}",
          @endforeach
        },
        rules: {
          'app.url': [
            {required:true, message:'不能为空', trigger:'submit'},
            {type:'url', message:'格式错误', trigger:'blur'},
          ],
          'mail.to.address': [
            {required:true, message:'不能为空', trigger:'submit'},
            {type:'email', message:'格式错误', trigger:'blur'},
          ],
          'jc.site.subject': [
            {required:true, message:'不能为空', trigger:'submit'},
          ],
        },
      };
    },

    created() {
      this.initial_data = clone(this.configs);
    },

    methods: {
      getChanged() {
        const changed = [];
        for (const key in this.configs) {
          if (! isEqual(this.configs[key], this.initial_data[key])) {
            changed.push(key);
          }
        }

        return changed;
      },

      useInTwig(name) {
        return `@{{ config('${name}') }}`;
      },

      submit() {
        let form = this.$refs.main_form;

        const loading = this.$loading({
          lock: true,
          text: '正在更新设置 ...',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        form.validate().then(() => {

          const changed = this.getChanged();
          if (! changed.length) {
            loading.close();
            this.$message.info('未作任何更改');
            return;
          }

          const configs = clone(this.configs);
          configs._changed = changed;

          axios.post("{{ short_url('configs.update') }}", configs).then(response => {
            loading.close();
            this.initial_data = clone(this.configs);
            // console.log(response);
            this.$message.success('设置已更新');
          }).catch(err => {
            loading.close();
            console.error(err);
            this.$message.error('发生错误，可查看控制台');
          });
        }).catch(() => {
          loading.close();
        })
      },
    },
  });
</script>
@endsection
