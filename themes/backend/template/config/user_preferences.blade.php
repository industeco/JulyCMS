@extends('backend::layout')

@section('h1', '偏好设置')

@section('main_content')
<el-form id="main_form" ref="main_form"
  :model="configs"
  label-position="top">
  <div id="main_form_left">
    <div class="el-form-item el-form-item--small jc-embeded-field {{ $configs['app.field_groups']['description']?'has-helptext':'' }}">
      <div class="el-form-item__content">
        <div class="jc-embeded-field__header">
          <el-tooltip popper-class="jc-twig-output" effect="dark" :content="useInTwig('app.field_groups')" placement="right">
            <label class="el-form-item__label">{{ $configs['app.field_groups']['label'] }}</label>
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
              <tr v-for="(data, key) in configs['app.field_groups']" :key="key">
                <td>@{{ key }}</td>
                <td>@{{ data['label'] }}</td>
                <td><el-switch v-model="data['expanded']"></el-switch></td>
              </tr>
            </tbody>
          </table>
        </div>
        @if ($configs['app.field_groups']['description'])
        <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $configs['app.field_groups']['description'] }}</span>
        @endif
      </div>
    </div>
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
          'app.field_groups': @json($configs['app.field_groups']['value'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
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
        }).catch((err) => {
          console.error(err);
          loading.close();
        })
      },
    },
  });
</script>
@endsection
