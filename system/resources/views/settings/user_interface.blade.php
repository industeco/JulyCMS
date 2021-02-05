@extends('layout')

@section('h1', $title)

@section('main_content')
<el-form id="main_form" ref="main_form"
  :model="settings"
  label-position="top">
  <div id="main_form_left">
    <div class="el-form-item el-form-item--small jc-embeded-field {{ $items['app.field_groups']['description']?'has-helptext':'' }}">
      <div class="el-form-item__content">
        <div class="jc-embeded-field__header">
          <el-tooltip popper-class="jc-twig-output" effect="dark" content="{!! $items['app.field_groups']['tips'] !!}" placement="right">
            <label class="el-form-item__label">{{ $items['app.field_groups']['label'] }}</label>
          </el-tooltip>
        </div>
        <div class="jc-table-wrapper">
          <table class="jc-table jc-dense is-draggable with-operators">
            <colgroup>
              <col width="40%">
              <col width="auto">
            </colgroup>
            <thead>
              <tr>
                <th>分组 id</th>
                <th>分组标签</th>
                <th>默认展开</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(data, key) in settings['app.field_groups']" :key="key">
                <td>@{{ key }}</td>
                <td>@{{ data['label'] }}</td>
                <td><el-switch v-model="data['expanded']"></el-switch></td>
              </tr>
            </tbody>
          </table>
        </div>
        @if ($items['app.field_groups']['description'])
        <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $items['app.field_groups']['description'] }}</span>
        @endif
      </div>
    </div>
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
            this.$message.error('发生错误，请查看后台日志');
          });
        }).catch((err) => {
          console.error(err);
          loading.close();
        });
      },
    },
  });
</script>
@endsection
