<template>
  <div class="l_main"
    v-loading.fullscreen.lock="loader">
    <div class="l_wrap">
      <div class="l_contents" :class="show">
        <el-form :model="registForm" ref="registForm">
        <h1 class="c_h1">{{ registForm.alertname }}の編集</h1>
        <div class="l_block">
          <table class="c_table_type01">
            <tr>
              <th class="u_ALright">メッセージ</th>
              <td>
                <template v-if="acl.message">
                  <el-form-item prop="message">
                    <el-input class="ext_block" type="textarea" v-model="registForm.message" name="message" cols="100" rows="7"></el-input>
                  </el-form-item>
                </template>
                <template v-else>
                  <div>{{registForm.message}}</div>
                </template>
              </td>
            </tr>
          </table>
        </div>
        <div class="l_block">
          <table class="c_table_type01">
            <tr>
              <th rowspan="2" class="u_ALright">受信者</th>
              <td>
                  <user-search @child-event="this.setUserInfo" ref="userSearch" ></user-search>
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <table class="inline_table" ref="" style="">
                  <tbody>
                    <tr v-for="(user, index) in userdata" v-bind:key="user.id">
                    <td>{{user.usercode}}</td>
                    <td width="70%">{{user.username}}</td>
                    <td>
                      <el-checkbox-group v-model="user.mailsendingflag">
                        <el-checkbox
                          :key="index"
                          :value="user.mailsendingflag"
                          :label="index">メール受信
                        </el-checkbox>
                      </el-checkbox-group>
                      </td>
                      <template v-if="acl.deleteBtn">
                        <td><a href="javascript:void(0);" class="c_btn c_btn_type01 is_blue c_modal_link" @click="Delete(index);">削除</a></td>
                      </template>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>
          </table>
        </div>
        <div class="l_block">
          <template v-if="acl.registBtn">
            <ul class="u_btn_list u_ALcenter">
              <li><a href="javascript:void(0);" class="c_btn c_btn_type01 is_orange" @click="Regist(registForm);">登録</a></li>
            </ul>
          </template>
        </div>
        </el-form>
        <!-- block end -->
        <!-- block end -->
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ManagesAlertDetail',
  data () {
    return {
      // 初期化ページのローダ
      loader: true,

      userdata: [],
      temp: [],

      // 権限設定
      acl: {
        message: true,
        deleteBtn: true,
        registBtn: true,
      },

      // 内容を表示OR非表示にする
      show: '',

      // 登録フォーム
      registForm: {
        id              : '', // アラートID
        alertname       : '', // アラート名称
        message         : '', // メッセージ
        userid          : '', // ユーザーID
        usercode        : '', // ユーザーコード
        username        : '', // ユーザー名
        mailsendingflag : '', // メール受信フラグ
      },
    }
  },
  created() {
    var me = this;

    console.log(this.userdata)
    // 内容を表示しない
    me.$data.show = 'display_none';

    // GETパラメータをオブジェクトで取得
    var num = me.getUrlParam();
    // オブジェクトの中身を判定
    var numCheck = Object.keys(num).length;

    // 詳細情報取得
    if (numCheck !== 0) {
      axios.post('/alert/detailValue', {id: num['id']}).then(function(response) {
        var res = response.data;

        // エラーがあった場合一覧へ
        if (res.st == 'ng') {
          window.location.href = '/alert/index';
          return;
        }

        // 詳細を画面に
        var rec = res.data;

        me.registForm = {
        id         : rec[0].ALERTID,         // アラートID
        alertname  : rec[0].ALERTNAME,       // アラート名称
        message    : rec[0].MESSAGE,         // メッセージ
        }
        if(res.common.msg == 1){
          var userDataCnt = res.data.length
          // 対象アラートに紐付いているユーザーをバインド
          if(userDataCnt > 0) {
            for (var i = 0; i < userDataCnt; i++) {
              if(rec[i].MAILSENDINGFLAG == 1){
                var checkflag = true;
              } else {
                var checkflag = false;
              }
              me.userdata.push({
                userid     : rec[i].USERID,
                usercode   : rec[i].CODE,
                username   : rec[i].NAME,
                mailsendingflag : checkflag
              });
            }
          }
        }
        // ローダを表示しない
        me.loader = false;

        // 内容を表示する
        me.$data.show = '';
      });
    }
  },

  methods: {
    /**
     * GETパラメータをオブジェクトで取得
     *
     * @return int number of the url
     */
    getUrlParam() {
      var vars = {};
      var param = location.search.substring(1).split('&');
      for(var i = 0; i < param.length; i++) {
        var keySearch = param[i].search(/=/);
        var key = '';
        if(keySearch != -1) key = param[i].slice(0, keySearch);
        var val = param[i].slice(param[i].indexOf('=', 0) + 1);
        if(key != '') vars[key] = decodeURI(val);
      }
      return vars;
    },

    /**
     * 追加ボタンクリック
     *
     * @return
     */
    Add() {
      if(this.temp.length !== 0){
        if(this.temp[0].name !=='' && this.temp[0].id !== ''){
          this.userdata.push({
            userid            : this.temp[0].id,
            username          : this.temp[0].name,
            mailsendingflag   : false
          })
          this.$refs.userSearch.clearValue();
        }
        this.temp = [];
      }
    },

    /**
     * 削除ボタンクリック
     * @param ユーザーのインデックス番号
     */
    Delete(index) {
      console.log(this.userdata);

      // コードもしくは名称の入力をクリアする
      this.$refs.userSearch.clearValue();

      this.userdata.splice(index, 1)
    },

    /**
     * 登録ボタンクリック
     * @param ユーザーデータ
     */
    Regist(registForm) {
      var me = this;

      // コードもしくは名称の入力をクリアする
      me.$refs.userSearch.clearValue();

      me.$confirm('登録します。よろしいですか？', '確認', {
        confirmButtonText: '登録',
        cancelButtonText: 'キャンセル',
        type: 'warning'
      }).then(() => {
        // ローダを表示する
        me.loader = true;

        // post the data if no error
        axios.post('/alert/regist', {alert: me.registForm, user:me.userdata}).then(function(response) {
          // ローダを表示しない
          me.loader = false;

          var res = response.data;

          if (res.common.st == 'ng') {
            me.$alert(res.common.msg, 'エラー', {
              confirmButtonText: 'OK',
              type: 'error',
              callback: action => { return; }
            });
          } else {
              me.$message(res.common.msg, '登録', {
                type: 'success'
              });
          }
        });
      }).catch(() => {});
    },

    /**
     * モーダルで選択したユーザー情報を一時領域に格納
     * @param object user userid, usercode, username, mailsendingflag
     */
    setUserInfo(user){
      // only get the object cause user parameter gives integer and null
      if (typeof user !== 'object' || user === null) {
        return;
      }

      // only get the object with value cause it gives object with empty values
      if (user.USERID === '' || user.USERNAME === '') {
        return;
      }

      // ユーザーデータの数を取得する
      let cnt = this.userdata.length;

      // テーブルでデータがあるかどうか
      if (cnt < 1) {
        this.userdata.push({
          usercode       : user.USERCODE,
          userid         : user.USERID,
          username       : user.USERNAME,
          mailsendingflag: false,
        });

        return;
      }

      // テーブルはデータがあればloopする
      for (let i = 0; i < cnt; i++) {
        // テーブルのユーザとコードもしくは名称の入力をcompareする
        if (this.userdata[i].userid !== user.USERID) {
            continue;
        }

        // duplicateがあればアラートが出して
        this.$alert('この受信者「'+this.userdata[i].usercode+'：'+this.userdata[i].username+'」は既に存在しています。', 'データ複製エラー', {
          confirmButtonText: 'OK',
          callback: action => {
            // remove the highlighted data
            this.userdata[i].duplicateStatus = '';
          }
        });

        // to stop the loop so it won't add to the table
        return;
      }

      // テーブルの一番下にデータを入れる
      this.userdata.push({
        usercode       : user.USERCODE,
        userid         : user.USERID,
        username       : user.USERNAME,
        mailsendingflag: false,
      });

      // コードもしくは名称の入力をクリアする
      this.$refs.userSearch.clearValue();
    },

  }
}
</script>

<style scoped>
.el-input {
  width: 100%;
  }
.inline_table tr,.inline_table td {
  border: 0px;
}
</style>