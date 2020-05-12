@extends('admin::layout')

@section('h1', '语言设置')

@section('main_content')
<el-form id="main_form" ref="main_form"
  :model="settings"
  label-position="top">
  <div id="main_form_left">
    <div class="el-form-item el-form-item--small jc-embeded-field {{ $settings['languages']['description']?'has-helptext':'' }}">
      <div class="el-form-item__content">
        <div class="jc-embeded-field__header">
          <label class="el-form-item__label">{{ $settings['languages']['label'] }}</label>
          <div class="jc-embeded-field__buttons">
            <el-select v-model="selected" placeholder="--选择语言--" size="small" filterable>
              <el-option
                v-for="(langname, langcode) in languages"
                :key="langcode"
                :label="langname"
                :value="langcode">
              </el-option>
            </el-select>
            <button type="button" class="md-button md-raised md-dense md-primary md-theme-default"
              @click="addLanguage">
              <div class="md-ripple">
                <div class="md-button-content">添加到列表</div>
              </div>
            </button>
          </div>
        </div>
        <div class="jc-table-wrapper">
          <table class="jc-table jc-dense is-draggable with-operators">
            <colgroup>
              <col width="100px">
              <col width="auto">
              <col width="150px">
              <col width="150px">
            </colgroup>
            <thead>
              <tr>
                <th>代码</th>
                <th>语言</th>
                <th>是否预设</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(langname, langcode) in settings.languages" :key="langcode">
                <td><span>@{{ langcode }}</span></td>
                <td><span>@{{ langname }}</span></td>
                <td><span>@{{ isPreset(langcode) ? '是' : '否' }}</span></td>
                <td>
                  <div class="jc-operators">
                    <button
                      type="button"
                      class="md-button md-icon-button md-accent md-theme-default"
                      title="删除"
                      :disabled="isPreset(langcode)"
                      @click="removeLanguage(langcode)">
                      <i class="md-icon md-icon-font md-theme-default">remove_circle</i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        @if ($settings['languages']['description'])
        <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $settings['languages']['description'] }}</span>
        @endif
      </div>
    </div>
    <el-form-item label="{{ $settings['content_lang']['label'] }}" prop="content_lang" size="small"
      class="{{ $settings['content_lang']['description']?'has-helptext':'' }}">
      <el-select v-model="settings.content_lang">
        <el-option
          v-for="(langname, langcode) in settings.languages"
          :key="langcode"
          :label="langname"
          :value="langcode">
        </el-option>
      </el-select>
      @if ($settings['content_lang']['description'])
      <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $settings['content_lang']['description'] }}</span>
      @endif
    </el-form-item>
    <el-form-item label="{{ $settings['site_page_lang']['label'] }}" prop="site_page_lang" size="small"
      class="{{ $settings['site_page_lang']['description']?'has-helptext':'' }}">
      <el-select v-model="settings.site_page_lang">
        <el-option
          v-for="(langname, langcode) in settings.languages"
          :key="langcode"
          :label="langname"
          :value="langcode">
        </el-option>
      </el-select>
      @if ($settings['site_page_lang']['description'])
      <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $settings['site_page_lang']['description'] }}</span>
      @endif
    </el-form-item>
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
          languages: @json($settings['languages']['value'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
          @foreach ($settings as $item)
          @if ($item['truename'] !== 'languages')
          {{ $item['truename'] }}: "{{ $item['value'] }}",
          @endif
          @endforeach
        },
        selected: null,
        languages: @json(language_list('zh'), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
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

      addLanguage() {
        const languages = clone(this.settings.languages);
        if (! languages[this.selected]) {
          languages[this.selected] = this.languages[this.selected];
          this.$set(this.settings, 'languages', languages);
        } else {
          this.$message.warning('已存在');
        }
      },

      removeLanguage(langcode) {
        const languages = clone(this.settings.languages);
        delete languages[langcode];
        this.$set(this.settings, 'languages', languages);
      },

      isPreset(langcode) {
        return langcode === 'en' || langcode === 'zh';
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

          axios.post('/admin/config/language', settings).then(response => {
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
