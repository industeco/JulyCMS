@extends('admin::layout')

@section('h1', '语言设置')

@section('main_content')
<el-form id="main_form" ref="main_form"
  :model="settings"
  label-position="top">
  <div id="main_form_left">
    <el-form-item prop="multi_language" size="small"
      class="{{ $settings['multi_language']['description']?'has-helptext':'' }}">
      <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" :content="useInTwig('multi_language')" placement="right">
        <span>{{ $settings['multi_language']['label'] }}</span>
      </el-tooltip>
      <el-switch
        v-model="settings['multi_language']"
        active-text="启用"
        inactive-text="不启用">
      </el-switch>
      @if ($settings['multi_language']['description'])
      <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $settings['multi_language']['description'] }}</span>
      @endif
    </el-form-item>
    <div class="el-form-item el-form-item--small jc-embeded-field {{ $settings['langcode.permissions']['description']?'has-helptext':'' }}"
      v-if="settings['multi_language']">
      <div class="el-form-item__content">
        <div class="jc-embeded-field__header">
          <el-tooltip popper-class="jc-twig-output" effect="dark" :content="useInTwig('langcode.permissions')" placement="right">
            <label class="el-form-item__label">{{ $settings['langcode.permissions']['label'] }}</label>
          </el-tooltip>
          <div class="jc-embeded-field__buttons">
            <el-select v-model="selected" placeholder="--选择语言--" size="small" filterable>
              <el-option
                v-for="(langname, langcode) in languageList"
                :key="langcode"
                :label="'['+langcode+'] '+langname"
                :value="langcode">
              </el-option>
            </el-select>
            <button type="button" class="md-button md-raised md-dense md-primary md-theme-default"
              @click="addLanguage" :disabled="!languageSelectable">
              <div class="md-ripple">
                <div class="md-button-content">添加到列表</div>
              </div>
            </button>
          </div>
        </div>
        <div class="jc-table-wrapper">
          <table class="jc-table jc-dense is-draggable with-operators">
            <colgroup>
              <col width="180px">
              <col width="auto">
              <col width="auto">
              <col width="100px">
            </colgroup>
            <thead>
              <tr>
                <th>语言 | 代码</th>
                <th>可翻译</th>
                <th>可访问</th>
                <th>删除</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(info, langcode) in settings['langcode.permissions']" :key="langcode">
                <td><span>@{{ languageList[langcode]+' | '+langcode }}</span></td>
                <td><el-switch v-model="info['content_value']" :disabled="langcode==='en'" @change="handleContentValueChange(langcode)"></el-switch></td>
                <td><el-switch v-model="info['site_page']" :disabled="langcode==='en'" @change="handleSitePageChange(langcode)"></el-switch></td>
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
        @if ($settings['langcode.permissions']['description'])
        <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $settings['langcode.permissions']['description'] }}</span>
        @endif
      </div>
    </div>
    <el-form-item size="small"
      class="{{ $settings['langcode.content_value']['description']?'has-helptext':'' }}"
      v-if="settings['multi_language']">
      <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" :content="useInTwig('langcode.content_value')" placement="right">
        <span>{{ $settings['langcode.content_value']['label'] }}</span>
      </el-tooltip>
      <el-select v-model="settings['langcode.content_value']">
        <el-option
          v-for="langcode in contentLangcodes"
          :key="langcode"
          :label="'['+langcode+'] '+languageList[langcode]"
          :value="langcode">
        </el-option>
      </el-select>
      @if ($settings['langcode.content_value']['description'])
      <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $settings['langcode.content_value']['description'] }}</span>
      @endif
    </el-form-item>
    <el-form-item size="small"
      class="{{ $settings['langcode.site_page']['description']?'has-helptext':'' }}"
      v-if="settings['multi_language']">
      <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" :content="useInTwig('langcode.site_page')" placement="right">
        <span>{{ $settings['langcode.site_page']['label'] }}</span>
      </el-tooltip>
      <el-select v-model="settings['langcode.site_page']">
        <el-option
          v-for="langcode in siteLangcodes"
          :key="langcode"
          :label="'['+langcode+'] '+languageList[langcode]"
          :value="langcode">
        </el-option>
      </el-select>
      @if ($settings['langcode.site_page']['description'])
      <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $settings['langcode.site_page']['description'] }}</span>
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
          'langcode.permissions': @json($settings['langcode.permissions']['value'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
          'multi_language': {{ $settings['multi_language']['value'] ? 'true' : 'false' }},
          'langcode.content_value': "{{ $settings['langcode.content_value']['value'] }}",
          'langcode.site_page': "{{ $settings['langcode.site_page']['value'] }}",
        },
        selected: null,
        languageList: @json(language_list(), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
      };
    },

    created() {
      this.initial_data = clone(this.settings);
    },

    computed: {
      languageSelectable() {
        const code = this.selected;
        return code && this.languageList[code] && !this.settings['langcode.permissions'][code];
      },

      contentLangcodes() {
        const list = this.settings['langcode.permissions'];
        const langcodes = [];
        for (const key in list) {
          if (list[key].content_value) {
            langcodes.push(key);
          }
        }
        return langcodes;
      },

      siteLangcodes() {
        const list = this.settings['langcode.permissions'];
        const langcodes = [];
        for (const key in list) {
          if (list[key].site_page) {
            langcodes.push(key);
          }
        }
        return langcodes;
      },
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
        if (!this.languageSelectable) {
          return;
        }
        const code = this.selected;
        const index = this.settings['langcode.permissions'][code];
        if (this.settings['langcode.permissions'][code]) {
          this.$message.warning('已存在');
        } else {
          this.settings['langcode.permissions'][code] = {
            content_value: true,
            site_page: true,
            interface_value: false,
            admin_page: false,
          };
        }
        this.selected = null;
      },

      removeLanguage(langcode) {
        if (this.settings['langcode.permissions'][langcode]) {
          const list = clone(this.settings['langcode.permissions']);
          delete list[langcode];
          this.$set(this.settings, 'langcode.permissions', list);
          // this.settings['langcode.permissions'].splice(index, 1);
        }

        this.resetDefaultLangcode('langcode.content_value', langcode);
        this.resetDefaultLangcode('langcode.site_page', langcode);
      },

      resetDefaultLangcode(key, langcode) {
        if (this.settings[key] === langcode) {
          this.settings[key] = this.initial_data[key];
        }
      },

      handleContentValueChange(langcode) {
        const status = this.settings['langcode.permissions'][langcode];
        if (status['site_page'] && !status['content_value']) {
          status['site_page'] = false;
        }

        if (!status['site_page']) {
          this.resetDefaultLangcode('langcode.site_page', langcode);
        }
        if (!status['content_value']) {
          this.resetDefaultLangcode('langcode.content_value', langcode);
        }
      },

      handleSitePageChange(langcode) {
        const status = this.settings['langcode.permissions'][langcode];
        if (status['site_page'] && !status['content_value']) {
          status['content_value'] = true;
        }
        if (!status['site_page']) {
          this.resetDefaultLangcode('langcode.site_page', langcode);
        }
      },

      useInTwig(name) {
        return `@{{ config('jc.${name}') }}`;
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
            this.initial_data = clone(this.settings);
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
