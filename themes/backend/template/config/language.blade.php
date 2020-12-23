@extends('backend::layout')

@section('h1', '语言设置')

@section('main_content')
<el-form id="main_form" ref="main_form"
  :model="configs"
  label-position="top">
  <div id="main_form_left">
    <el-form-item prop="jc.language.multiple" size="small"
      class="{{ $configs['jc.language.multiple']['description']?'has-helptext':'' }}">
      <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" :content="useInTwig('jc.language.multiple')" placement="right">
        <span>{{ $configs['jc.language.multiple']['label'] }}</span>
      </el-tooltip>
      <el-switch
        v-model="configs['jc.language.multiple']"
        active-text="启用"
        inactive-text="不启用">
      </el-switch>
      @if ($configs['jc.language.multiple']['description'])
      <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $configs['jc.language.multiple']['description'] }}</span>
      @endif
    </el-form-item>
    <div class="el-form-item el-form-item--small jc-embeded-field {{ $configs['jc.language.list']['description']?'has-helptext':'' }}"
      v-if="configs['jc.language.multiple']">
      <div class="el-form-item__content">
        <div class="jc-embeded-field__header">
          <el-tooltip popper-class="jc-twig-output" effect="dark" :content="useInTwig('jc.language.list')" placement="right">
            <label class="el-form-item__label">{{ $configs['jc.language.list']['label'] }}</label>
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
              @click.stop="addLanguage" :disabled="!isLangcodeSelectable">
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
                <th>语言 [代码]</th>
                <th>可翻译</th>
                <th>可访问</th>
                <th>删除</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(info, langcode) in configs['jc.language.list']" :key="langcode">
                <td><span>@{{ languageList[langcode]+' ['+langcode+']' }}</span></td>
                <td><el-switch v-model="info['translatable']" :disabled="langcode==='en'" @change="handleTranslatableChange(langcode)"></el-switch></td>
                <td><el-switch v-model="info['accessible']" :disabled="langcode==='en'" @change="handleAccessibleChange(langcode)"></el-switch></td>
                <td>
                  <div class="jc-operators">
                    <button
                      type="button"
                      class="md-button md-icon-button md-accent md-theme-default"
                      title="删除"
                      :disabled="isPreset(langcode)"
                      @click.stop="removeLanguage(langcode)">
                      <i class="md-icon md-icon-font md-theme-default">remove_circle</i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        @if ($configs['jc.language.list']['description'])
        <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $configs['jc.language.list']['description'] }}</span>
        @endif
      </div>
    </div>
    <el-form-item size="small"
      class="{{ $configs['jc.language.content']['description']?'has-helptext':'' }}"
      v-if="configs['jc.language.multiple']">
      <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" :content="useInTwig('jc.language.content')" placement="right">
        <span>{{ $configs['jc.language.content']['label'] }}</span>
      </el-tooltip>
      <el-select v-model="configs['jc.language.content']">
        <el-option
          v-for="langcode in translatableLangcodes"
          :key="langcode"
          :label="'['+langcode+'] '+languageList[langcode]"
          :value="langcode">
        </el-option>
      </el-select>
      @if ($configs['jc.language.content']['description'])
      <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $configs['jc.language.content']['description'] }}</span>
      @endif
    </el-form-item>
    <el-form-item size="small"
      class="{{ $configs['jc.language.frontend']['description']?'has-helptext':'' }}"
      v-if="configs['jc.language.multiple']">
      <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" :content="useInTwig('jc.language.frontend')" placement="right">
        <span>{{ $configs['jc.language.frontend']['label'] }}</span>
      </el-tooltip>
      <el-select v-model="configs['jc.language.frontend']">
        <el-option
          v-for="langcode in accessibleLangcodes"
          :key="langcode"
          :label="'['+langcode+'] '+languageList[langcode]"
          :value="langcode">
        </el-option>
      </el-select>
      @if ($configs['jc.language.frontend']['description'])
      <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $configs['jc.language.frontend']['description'] }}</span>
      @endif
    </el-form-item>
    <div id="main_form_bottom" class="is-button-item">
      <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click.stop="submit">
        <div class="md-button-content">保存</div>
      </button>
    </div>
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
          'jc.language.multiple': {{ $configs['jc.language.multiple']['value'] ? 'true' : 'false' }},
          'jc.language.list': @json($configs['jc.language.list']['value'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
          'jc.language.content': "{{ $configs['jc.language.content']['value'] }}",
          'jc.language.frontend': "{{ $configs['jc.language.frontend']['value'] }}",
        },
        selected: null,
        languageList: @json(lang()->getLanguageList(), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
      };
    },

    created() {
      this.initial_data = clone(this.configs);
    },

    computed: {
      isLangcodeSelectable() {
        const code = this.selected;
        return code && this.languageList[code] && !this.configs['jc.language.list'][code];
      },

      translatableLangcodes() {
        const list = this.configs['jc.language.list'];
        const langcodes = [];
        for (const key in list) {
          if (list[key].translatable) {
            langcodes.push(key);
          }
        }
        return langcodes;
      },

      accessibleLangcodes() {
        const list = this.configs['jc.language.list'];
        const langcodes = [];
        for (const key in list) {
          if (list[key].accessible) {
            langcodes.push(key);
          }
        }
        return langcodes;
      },
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

      addLanguage() {
        if (!this.isLangcodeSelectable) {
          return;
        }
        const code = this.selected;
        if (this.configs['jc.language.list'][code]) {
          this.$message.warning('已存在');
        } else {
          const list = clone(this.configs['jc.language.list']);
          list[code] = {
            translatable: true,
            accessible: true,
          };
          this.$set(this.configs, 'jc.language.list', list);
        }
        this.selected = null;
      },

      removeLanguage(langcode) {
        if (this.configs['jc.language.list'][langcode]) {
          const list = clone(this.configs['jc.language.list']);
          delete list[langcode];
          this.$set(this.configs, 'jc.language.list', list);
        }

        this.resetDefaultLangcode('jc.language.content', langcode);
        this.resetDefaultLangcode('jc.language.frontend', langcode);
      },

      resetDefaultLangcode(key, langcode) {
        if (this.configs[key] === langcode) {
          this.configs[key] = this.initial_data[key];
        }
      },

      handleTranslatableChange(langcode) {
        const status = this.configs['jc.language.list'][langcode];
        if (status['accessible'] && !status['translatable']) {
          status['accessible'] = false;
        }

        if (!status['accessible']) {
          this.resetDefaultLangcode('jc.language.frontend', langcode);
        }
        if (!status['translatable']) {
          this.resetDefaultLangcode('jc.language.content', langcode);
        }
      },

      handleAccessibleChange(langcode) {
        const status = this.configs['jc.language.list'][langcode];
        if (status['accessible'] && !status['translatable']) {
          status['translatable'] = true;
        }
        if (!status['accessible']) {
          this.resetDefaultLangcode('jc.language.frontend', langcode);
        }
      },

      useInTwig(name) {
        return `@{{ config('${name}') }}`;
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
