@extends('admin::layout')

@section('h1', '网站基础设置')

@section('main_content')
<el-form id="main_form" ref="main_form"
  :model="configs"
  :rules="rules"
  label-position="top">
  <div id="main_form_left">
    @foreach ($configs as $keyname => $config)
    <el-form-item prop="{{ $keyname }}" size="small"
      class="{{ $config['description']?'has-helptext':'' }}">
      <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" :content="useInTwig('{{ $keyname }}')" placement="right">
        <span>{{ $config['label'] }}</span>
      </el-tooltip>
      <el-input
        v-model="configs.{{ $keyname }}"
        native-size="80"></el-input>
        @if ($config['description'])
        <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $config['description'] }}</span>
        @endif
    </el-form-item>
    @endforeach
    <div id="main_form_bottom" class="is-button-item">
      <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click="submit">
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
          @foreach ($configs as $item)
          "{{ $item['keyname'] }}": "{{ $item['value'] }}",
          @endforeach
        },
        rules: {
          url: [
            {required:true, message:'不能为空', trigger:'submit'},
            {type:'url', message:'格式错误', trigger:'blur'},
          ],
          email: [
            {required:true, message:'不能为空', trigger:'submit'},
            {type:'email', message:'格式错误', trigger:'blur'},
          ],
          owner: [
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
        return `@{{ config('jc.${name}') }}`;
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

          axios.post("{{ short_route('configs.update') }}", configs).then(response => {
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