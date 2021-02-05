@extends('layout')

@section('h1', $title)

@section('main_content')
<el-form id="main_form" ref="main_form"
  :model="settings"
  label-position="top">
  <div id="main_form_left">
    <el-form-item prop="language.multiple" size="small"
      class="{{ $items['language.multiple']['description']?'has-helptext':'' }}">
      <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" content="{!! $items['language.multiple']['tips'] !!}" placement="right">
        <span>{{ $items['language.multiple']['label'] }}</span>
      </el-tooltip>
      <el-switch
        v-model="settings['language.multiple']"
        active-text="启用"
        inactive-text="不启用">
      </el-switch>
      @if ($items['language.multiple']['description'])
      <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $items['language.multiple']['description'] }}</span>
      @endif
    </el-form-item>
    <div class="el-form-item el-form-item--small jc-embeded-field {{ $items['language.available']['description']?'has-helptext':'' }}"
      v-if="settings['language.multiple']">
      <div class="el-form-item__content">
        <div class="jc-embeded-field__header">
          <el-tooltip popper-class="jc-twig-output" effect="dark" content="{!! $items['language.available']['tips'] !!}" placement="right">
            <label class="el-form-item__label">{{ $items['language.available']['label'] }}</label>
          </el-tooltip>
          <div class="jc-embeded-field__buttons">
            <el-select v-model="selected" placeholder="--选择语言--" size="small" filterable>
              <el-option
                v-for="(langname, langcode) in langnames"
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
              <tr v-for="(info, langcode) in settings['language.available']" :key="langcode">
                <td><span>@{{ langnames[langcode]+' ['+langcode+']' }}</span></td>
                <td><el-switch v-model="info['translatable']" :disabled="langcode==='en'" @change="handleTranslatableChange(langcode)"></el-switch></td>
                <td><el-switch v-model="info['accessible']" :disabled="langcode==='en'" @change="handleAccessibleChange(langcode)"></el-switch></td>
                <td>
                  <div class="jc-operators">
                    <button
                      type="button"
                      class="md-button md-icon-button md-accent md-theme-default"
                      title="删除"
                      :disabled="isReserved(langcode)"
                      @click.stop="removeLanguage(langcode)">
                      <i class="md-icon md-icon-font md-theme-default">remove_circle</i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        @if ($items['language.available']['description'])
        <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $items['language.available']['description'] }}</span>
        @endif
      </div>
    </div>
    <el-form-item size="small"
      class="{{ $items['language.content']['description']?'has-helptext':'' }}"
      v-if="settings['language.multiple']">
      <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" content="{!! $items['language.content']['tips'] !!}" placement="right">
        <span>{{ $items['language.content']['label'] }}</span>
      </el-tooltip>
      <el-select v-model="settings['language.content']">
        <el-option
          v-for="langcode in translatableLangcodes"
          :key="langcode"
          :label="'['+langcode+'] '+langnames[langcode]"
          :value="langcode">
        </el-option>
      </el-select>
      @if ($items['language.content']['description'])
      <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $items['language.content']['description'] }}</span>
      @endif
    </el-form-item>
    <el-form-item size="small"
      class="{{ $items['language.frontend']['description']?'has-helptext':'' }}"
      v-if="settings['language.multiple']">
      <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" content="{!! $items['language.frontend']['tips'] !!}" placement="right">
        <span>{{ $items['language.frontend']['label'] }}</span>
      </el-tooltip>
      <el-select v-model="settings['language.frontend']">
        <el-option
          v-for="langcode in accessibleLangcodes"
          :key="langcode"
          :label="'['+langcode+'] '+langnames[langcode]"
          :value="langcode">
        </el-option>
      </el-select>
      @if ($items['language.frontend']['description'])
      <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $items['language.frontend']['description'] }}</span>
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
        settings: @json($settings, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        selected: null,
        langnames: @json(lang()->getAvailableLangnames(), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
      };
    },

    created() {
      this.original_settings = _.cloneDeep(this.settings);
    },

    computed: {
      isLangcodeSelectable() {
        const code = this.selected;
        return code && this.langnames[code] && !this.settings['language.available'][code];
      },

      translatableLangcodes() {
        const list = this.settings['language.available'];
        const langcodes = [];
        for (const key in list) {
          if (list[key].translatable) {
            langcodes.push(key);
          }
        }
        return langcodes;
      },

      accessibleLangcodes() {
        const list = this.settings['language.available'];
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
      // 添加语言到可用列表
      addLanguage() {
        if (!this.isLangcodeSelectable) {
          return;
        }
        const code = this.selected;
        if (this.settings['language.available'][code]) {
          this.$message.warning('已存在');
        } else {
          const list = _.cloneDeep(this.settings['language.available']);
          list[code] = {
            translatable: true,
            accessible: true,
          };
          this.$set(this.settings, 'language.available', list);
        }
        this.selected = null;
      },

      // 从可用列表移除指定语言
      removeLanguage(langcode) {
        if (this.settings['language.available'][langcode]) {
          const list = _.cloneDeep(this.settings['language.available']);
          delete list[langcode];
          this.$set(this.settings, 'language.available', list);
        }

        this.resetDefaultLangcode('language.content', langcode);
        this.resetDefaultLangcode('language.frontend', langcode);
      },

      // 如果设置值指向了一个不可用的语言（已从可用列表移除），则重置为初始值
      resetDefaultLangcode(key, langcode) {
        if (this.settings[key] === langcode) {
          this.settings[key] = this.original_settings[key];
        }
      },

      // 响应语言可访问性改变事件
      handleAccessibleChange(langcode) {
        const config = this.settings['language.available'][langcode];
        if (config['accessible'] && !config['translatable']) {
          config['translatable'] = true;
        }
        if (!config['accessible']) {
          this.resetDefaultLangcode('language.frontend', langcode);
        }
      },

      // 响应语言可翻译性改变事件
      handleTranslatableChange(langcode) {
        const status = this.settings['language.available'][langcode];
        if (config['accessible'] && !config['translatable']) {
          config['accessible'] = false;
        }

        if (!config['accessible']) {
          this.resetDefaultLangcode('language.frontend', langcode);
        }
        if (!config['translatable']) {
          this.resetDefaultLangcode('language.content', langcode);
        }
      },

      // 是否预留设置，预留设置不可更改
      isReserved(langcode) {
        return langcode === 'en' || langcode === 'zh';
      },

      // 提交
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
