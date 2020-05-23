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
          <el-tooltip popper-class="jc-twig-output" effect="dark" :content="useInTwig('languages')" placement="right">
            <label class="el-form-item__label">{{ $settings['languages']['label'] }}</label>
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
              <tr v-for="langcode in settings.languages" :key="langcode">
                <td><span>@{{ langcode }}</span></td>
                <td><span>@{{ languageList[langcode] }}</span></td>
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
    @foreach (array_diff_key($settings, ['languages'=>1]) as $item)
    <el-form-item prop="{{ $item['truename'] }}" size="small"
      class="{{ $item['description']?'has-helptext':'' }}">
      <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" :content="useInTwig('{{ $item['truename'] }}')" placement="right">
        <span>{{ $item['label'] }}</span>
      </el-tooltip>
      <el-select v-model="settings.{{ $item['truename'] }}">
        <el-option
          v-for="langcode in settings.languages"
          :key="langcode"
          :label="'['+langcode+'] '+languageList[langcode]"
          :value="langcode">
        </el-option>
      </el-select>
      @if ($item['description'])
      <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $item['description'] }}</span>
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
          languages: @json($settings['languages']['value'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
          @foreach (array_diff_key($settings, ['languages'=>1]) as $item)
          {{ $item['truename'] }}: "{{ $item['value'] }}",
          @endforeach
        },
        selected: null,
        languageList: @json(language_list('zh'), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
      };
    },

    created() {
      this.initial_data = clone(this.settings);
    },

    computed: {
      languageSelectable() {
        const code = this.selected;
        return code && this.languageList[code] && this.settings.languages.indexOf(code) < 0;
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
        const index = this.settings.languages.indexOf(code);
        if (index < 0) {
          this.settings.languages.push(code)
        } else {
          this.$message.warning('已存在');
        }
        this.selected = null;
      },

      removeLanguage(langcode) {
        const index = this.settings.languages.indexOf(langcode);
        if (index >= 0) {
          this.settings.languages.splice(index, 1);
        }

        for (const key in this.settings) {
          this.settings[key] === langcode;
          this.settings[key] = this.initial_data[key];
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
