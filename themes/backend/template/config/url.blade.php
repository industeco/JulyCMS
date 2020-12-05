@extends('backend::layout')

@section('h1', '语言设置')

@section('main_content')
<el-form id="main_form" ref="main_form"
  :model="configs"
  label-position="top">
  <div id="main_form_left">
    <div class="el-form-item el-form-item--small jc-embeded-field has-helptext">
      <div class="el-form-item__content">
        <div class="jc-embeded-field__header">
          <label class="el-form-item__label">网址重定向</label>
          <div class="jc-embeded-field__buttons">
            <button
              type="button"
              class="md-button md-icon-button md-primary md-theme-default"
              @click.stop="addRedirection">
              <i class="md-icon md-icon-font md-theme-default">add_circle</i>
            </button>
          </div>
        </div>
        <div class="jc-table-wrapper">
          <table class="jc-table jc-dense is-editable with-operators with-line-number">
            <colgroup>
              <col width="80px">
              <col width="auto">
              <col width="auto">
              <col width="120px">
              <col width="120px">
            </colgroup>
            <thead>
              <tr>
                <th>行号</th>
                <th>网址</th>
                <th>重定向</th>
                <th>状态码</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(redirection, index) in configs.redirections" :key="index">
                <th>@{{ index + 1 }}</th>
                <td>
                  <input type="text" class="jc-input-intable" v-model="redirection.from">
                </td>
                <td>
                  <input type="text" class="jc-input-intable" v-model="redirection.to">
                </td>
                <td>
                  <select class="jc-input-intable" v-model="redirection.code" size="small">
                    <option value="301">301</option>
                    <option value="302" selected>302</option>
                  </select>
                </td>
                <td>
                  <div class="jc-operators">
                    <button
                      type="button"
                      class="md-button md-icon-button md-accent md-theme-default"
                      @click.stop="removeRedirection(index)">
                      <i class="md-icon md-icon-font md-theme-default">remove_circle</i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <span class="jc-form-item-help"><i class="el-icon-info"></i> 添加 301，302 重定向</span>
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
  const redirections = [
    @foreach(config('redirections', []) as $from => $to)
    {
      from: `{{ $from }}`,
      to: `{{ $to['to'] }}`,
      code: {{ $to['code'] ?? 302 }},
    },
    @endforeach
  ];

  const app = new Vue({
    el: '#main_content',
    data() {
      return {
        configs: {
          redirections: redirections,
        },
      };
    },

    created() {
      this.initial_data = clone(this.configs);
    },

    methods: {
      addRedirection() {
        this.configs.redirections.push({
          from: '',
          to: '',
          code: 302,
        });
      },

      removeRedirection(index) {
        this.configs.redirections.splice(index, 1);
      },

      getChanged() {
        this.cleanRedirections();
        const changed = [];
        for (const key in this.configs) {
          if (! isEqual(this.configs[key], this.initial_data[key])) {
            changed.push(key);
          }
        }
        return changed;
      },

      cleanRedirections() {
        const redirections = [];
        this.configs.redirections.forEach(item => {
          if (item.from.trim() && item.to.trim()) {
            redirections.push({
              from: item.from.trim(),
              to: item.to.trim(),
              code: parseInt(item.code) || 302,
            });
          }
        });

        this.$set(this.$data.configs, 'redirections', redirections);
      },

      transformRedirections() {
        const redirections = {};
        this.configs.redirections.forEach(item => {
          if (item.from.trim() && item.to.trim()) {
            redirections[item.from.trim()] = {
              to: item.to.trim(),
              code: parseInt(item.code) || 302,
            };
          }
        });
        return redirections;
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
          configs.redirections = this.transformRedirections();

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
