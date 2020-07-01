<template>
  <div class="l_main"
    v-loading.fullscreen.lock="initializeLoader">
    <div class="l_wrap"
      v-loading.fullscreen.lock="submitLoader">
      <div class="l_contents" :class="show">
        <h1 class="c_h1">センター管理　{{ pgTitle }}</h1>
        <div class="l_block">
          <el-form :model="registForm" :rules="registRules" ref="registForm">
            <table class="c_table_type01">
              <tr v-if="registForm.centerid">
                <th class="u_ALright">センターID　</th>
                <td>{{ registForm.centerid }}</td>
              </tr>
              <tr>
                <th class="u_ALright">センター名<span class="u_req">※</span></th>
                <td>
                  <el-form-item prop="centername" class="ext_block">
                    <el-input v-model="registForm.centername"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <th class="u_ALright">郵便番号<span class="u_req">※</span></th>
                <td>
                  <el-form-item prop="zipcode" class="ext_block">
                    <el-input v-model="registForm.zipcode" maxlength="7" @input="getZipcode(registForm.zipcode, 'registForm')" v-loading="registForm.zipcodeLoader"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <th class="u_ALright">住所１（都道府県）<span class="u_req">※</span></th>
                <td>
                  <el-form-item prop="ADDRESS1" class="ext_block">
                    <el-select clearable v-model="registForm.ADDRESS1" placeholder="選択してください">
                      <el-option
                        v-for="item in prefecture"
                        :key="item.CODE"
                        :label="item.LOCALGOVERNMENTNAME"
                        :value="item.CODE">
                      </el-option>
                    </el-select>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <th class="u_ALright">住所２（市区町村）<span class="u_req">※</span></th>
                <td>
                  <el-form-item prop="ADDRESS2" class="ext_block">
                    <el-input v-model="registForm.ADDRESS2"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <th class="u_ALright">住所３（町域）　</th>
                <td>
                  <el-form-item prop="ADDRESS3" class="ext_block">
                    <el-input v-model="registForm.ADDRESS3"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <th class="u_ALright">住所４　</th>
                <td>
                  <el-form-item prop="address4" class="ext_block">
                    <el-input v-model="registForm.address4"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <th class="u_ALright">電話番号<span class="u_req">※</span></th>
                <td>
                  <el-form-item prop="tel" class="ext_block">
                    <el-input v-model="registForm.tel"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <th class="u_ALright">FAX番号　</th>
                <td>
                  <el-form-item prop="fax" class="ext_block">
                    <el-input v-model="registForm.fax"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <th class="u_ALright">メールアドレス　</th>
                <td>
                  <el-form-item prop="mail" class="ext_block">
                    <el-input v-model="registForm.mail"></el-input>
                  </el-form-item>
                </td>
              </tr>
              <tr>
                <th class="u_ALright">担当者名<span class="u_req">※</span></th>
                <td>
                  <el-form-item prop="stuffname" class="ext_block">
                    <el-input v-model="registForm.stuffname"></el-input>
                  </el-form-item>
                </td>
              </tr>
            </table>
          </el-form>
        </div>
        <!-- block end -->
        <div class="l_block">
          <ul class="u_btn_list u_ALcenter">
            <template v-if="acl.regist">
              <li>
                <a href="javascript:void(0);" class="c_btn c_btn_type01 is_orange" @click="onRegist()">登録</a>
              </li>
            </template>
            <template v-if="acl.delete">
              <li v-if="registForm.centerid">
                <a href="javascript:void(0);" class="c_btn c_btn_type01 is_orange" @click="onDelete()">削除</a>
              </li>
            </template>
            <li>
              <a href="/center/index" class="c_btn c_btn_type01">戻る</a>
            </li>
          </ul>
        </div>
        <!-- block end -->
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ManagesCenterEdit',
  data () {
    // 住所１（都道府県）
    var prefecture = [];

    // 必須機能
    var reqFunc = (rule, value, callback) => {
      // 住所１（都道府県） 選択ボックス
      if (rule.field === 'ADDRESS1' && value === '') {
        callback(new Error('選択してください。'));
      }

      // 入力テキストボックス
      if (value === '') {
        callback(new Error('入力してください。'));
      }

      callback();
    };

    // 必要なルール
    var req = [{required: true, validator: reqFunc, trigger: 'false'}];

    return {
      // ページタイトル
      pgTitle: '',

      // 登録ボタンのローダ
      submitLoader: false,

      // 初期化ページのローダ
      initializeLoader: true,

      // 住所１（都道府県）
      prefecture,

      // 内容を表示OR非表示にする
      show: '',

      // 登録フォーム
      registForm: {
        centerid  : '', // センターID
        centername: '', // センター名
        zipcode   : '', // 郵便番号
        ADDRESS1  : '', // 住所１（都道府県）
        ADDRESS2  : '', // 住所２（市区町村）
        ADDRESS3  : '', // 住所３（町域）
        address4  : '', // 住所４
        tel       : '', // 電話番号
        fax       : '', // FAX番号
        mail      : '', // メールアドレス
        stuffname : '', // 担当者名
        type      : '', // update or register
        zipcodeLoader: false, // 郵便番号のローダー
      },

      // 登録ルール
      registRules: {
        centerid  : req,
        centername: req,
        zipcode   : req,
        ADDRESS1  : req,
        ADDRESS2  : req,
        tel       : req,
        stuffname : req,
      },

      // 権限設定
      acl: {
        regist: true, // 更新
        delete: true, // 削除
      },
    }
  },
  created() {
    var me = this;

    // 内容を表示しない
    me.$data.show = 'display_none';

    // 住所１（都道府県）
    axios.get('/common/getprefecture').then(function(response) {
      me.prefecture = response.data;
    });

    // URL番号パラメータ
    var num = me.getUrlParam();

    // idパラメータと"id"がなければ一覧ページに戻る
    if (num == false) {
      window.location.href = '/center/index';
      return;
    }

    // ページタイトル
    me.pgTitle = num !== null?'詳細編集':'新規登録';

    // 詳細更新情報
    if (num !== null) {
      // センターIDからデータを取得
      axios.post('/center/detailValue', {num: num[0], 'type':'edit'}).then(function(response) {
        var res = response.data;

        // エラーがあったら、一覧ページに戻る
        if (res.st == 'ng') {
          window.location.href = '/center/index';
          return;
        }

        // 入力テキストボックスにデータを入れる
        var rec = res.data[0];
        me.registForm = {
          centerid  : rec.CENTERID,   // センターID
          centername: rec.CENTERNAME, // センター名
          zipcode   : rec.ZIPCODE,    // 郵便番号
          ADDRESS1  : rec.ADDRESS1,   // 住所１（都道府県）
          ADDRESS2  : rec.ADDRESS2,   // 住所２（市区町村）
          ADDRESS3  : rec.ADDRESS3,   // 住所３（町域）
          address4  : rec.ADDRESS4,   // 住所４
          tel       : rec.TEL,        // 電話番号
          fax       : rec.FAX,        // FAX番号
          mail      : rec.MAIL,       // メールアドレス
          stuffname : rec.STUFFNAME,  // 担当者名
          type      : 'update'
        }

        // ローダを表示しない
        me.initializeLoader = false;

        // 内容を表示する
        me.$data.show = '';
      });
    } else {
      // ローダを表示しない
        me.initializeLoader = false;

        // 内容を表示する
        me.$data.show = '';
    }
  },
  methods: {
    /**
     * フォームをSUBMITする
     * (regist)登録に成功した後、詳細ページに移動します。
     * で(update)更新のタイプとエラーがあるの場合はダイアログを
     * 表示されます。
     */
    onRegist() {
      var me = this;

      me.$refs['registForm'].validate(function(valid) {
        if (!valid) {
          me.$alert('入力に間違いがあります。 入力内容をご確認ください。', 'エラー', {
            confirmButtonText: 'OK',
            type: 'error',
            callback: action => { return; }
          });
          return false;
        }

        me.$confirm('登録します。よろしいですか？', '確認', {
          confirmButtonText: '登録',
          cancelButtonText: 'キャンセル',
          type: 'warning'
        }).then(() => {
          // ローダを表示する
          me.submitLoader = true;

          // 登録処理
          axios.post('/center/regist', me.registForm).then(function(response) {
            // ローダを表示しない
            me.submitLoader = false;

            // POSTレスポンスを取得する
            var res = response.data;

            // エラーがあればアラートメッセージを表示される
            if (res.st == 'ng') {
              // エラーメッセージを表示する
              me.$alert(res.data.msg, 'エラー', {
                confirmButtonText: 'OK',
                type: 'error',
                callback: action => { return; }
              });
            } else {
              // (regist)登録に成功した後、詳細ページに移動します。
              if (res.data.type == 'regist') {
                window.location.href = '/center/detail?id='+res.data.centerid;
                return;
              } else {
                // 更新の場合
                me.$message({
                  message: '編集が完了しました。',
                  type: 'success'
                });
              }
            }
          });
        }).catch(() => {});
      });

    },

    /**
     * 削除
     */
    onDelete() {
      var me = this;
      me.$confirm('削除します。よろしいですか？', '削除', {
        confirmButtonText: '削除',
        cancelButtonText: 'キャンセル',
        type: 'warning'
      }).then(() => {
        // ローダを表示する
        me.submitLoader = true;

        // 削除処理
        axios.post('/center/delete', me.registForm).then(function(response) {
          // POSTレスポンスを取得する
          var res = response.data;

          // エラーがあればアラートメッセージを表示される
          if (res.st == 'ng') {
            // ローダを表示しない
            me.submitLoader = false;

            // エラーメッセージを表示する
            me.$alert(res.data.msg, 'エラー', {
              confirmButtonText: 'OK',
              type: 'error',
              callback: action => { return; }
            });
            return;
          } else {
            // 一覧ページへ
            window.location.href = '/center/index';
            return;
          }
        });
      }).catch(() => {});
    },

    /**
     * パラメータ番号のURLを取得する
     *
     * @return int URLの番号
     */
    getUrlParam() {
      var field = 'id';

      // 現在のURLを取得
      var param = window.location.href.split('/').pop();

      // URLにIDがあるかどうかを確認
      var id  = param.indexOf('?' + field + '=') != -1;

      // URLに番号があるかどうか確認
      var num   = param.match(/\d+$/);

      if (num !== null && id == false) {
        return false;
      } else if (id != false && num == null) {
        return false;
      }

      return num;
    },
  },
}
</script>

<style scoped>
.display_none {
  display: none;
}
</style>