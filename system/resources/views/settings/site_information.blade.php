@extends('layout')

@section('h1', $title)

@section('main_content')
<el-form id="main_form" ref="main_form"
  :model="settings"
  :rules="rules"
  label-position="top">
  <div id="main_form_left">
    @foreach ($items as $key => $item)
    <el-form-item prop="{{ $key }}" size="small"
      class="{{ isset($item['description'])?'has-helptext':'' }}">
      <el-tooltip slot="label" content="{!! $item['tips'] !!}" popper-class="jc-twig-output" effect="dark" placement="right">
        <span>{{ $item['label'] }}</span>
      </el-tooltip>
      <el-input
        v-model="settings['{{ $key }}']"
        native-size="80"></el-input>
        @if (isset($item['description']))
        <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $item['description'] }}</span>
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
        settings: @json($settings, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        rules: {
          'app.url': [
            {required:true, message:'不能为空', trigger:'submit'},
            {type:'url', message:'格式错误', trigger:'blur'},
          ],
          'mail.to.address': [
            {required:true, message:'不能为空', trigger:'submit'},
            {type:'email', message:'格式错误', trigger:'blur'},
          ],
          'site.subject': [
            {required:true, message:'不能为空', trigger:'submit'},
          ],
        },
      };
    },

    created() {
      this.original_settings = _.cloneDeep(this.settings);
    },

    methods: {
      submit() {
        if (_.isEqual(this.settings, this.original_settings)) {
          this.$message.info('未作任何更改');
          return;
        }

        const loading = this.$loading({
          lock: true,
          text: '正在更新设置 ...',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        this.$refs.main_form.validate().then(() => {
          axios.post("{{ short_url('settings.update', $name) }}", this.settings).then(response => {
            loading.close();
            this.original_settings = _.cloneDeep(this.settings);
            // console.log(response);
            this.$message.success('设置已更新');
          }).catch(err => {
            loading.close();
            console.error(err);
            this.$message.error('发生错误，可查看控制台');
          });
        }).catch(() => {
          loading.close();
        });
      },
    },
  });
</script>
@endsection
