@extends('admin::layout')

@section('h1', '网站基础设置')

@section('main_content')
<el-form id="main_form" ref="main_form"
  :model="settings"
  :rules="rules"
  label-position="top">
  <div id="main_form_left">
    @foreach (['owner','url','email'] as $name)
    <el-form-item label="{{ $settings[$name]['label'] }}" prop="{{ $name }}" size="small"
      class="{{ $settings[$name]['description']?'has-helptext':'' }}">
      <el-input
        v-model="settings.{{ $name }}"
        native-size="80"></el-input>
        @if ($settings[$name]['description'])
        <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $settings[$name]['description'] }}</span>
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
        settings: {
          @foreach ($settings as $item)
          {{ $item['truename'] }}: "{{ $item['value'] }}",
          @endforeach
        },
        rules: {
          url: [
            {required:true, message:'不能为空', trigger:'change'},
            {type:'url', message:'网址格式不正确', trigger:'blur'},
          ],
          email: [
            {required:true, message:'不能为空', trigger:'submit'},
            {type:'email', message:'邮箱格式不正确', trigger:'submit'},
          ],
          owner: [
            {required:true, message:'不能为空', trigger:'submit'},
          ],
        },
      };
    },

    created() {
      this.initial_data = clone(this.settings);
    },

    methods: {
      getChanged() {
        const changed = [];
        for (const key in this.settings) {
          if (! isEqual(this.settings[key], this.initial_data[key])) {
            changed.push(key);
          }
        }

        return changed;
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

          const settings = clone(this.settings);
          settings._changed = changed;

          axios.post('/admin/config/basic', settings).then(response => {
            loading.close();
            console.log(response);
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
