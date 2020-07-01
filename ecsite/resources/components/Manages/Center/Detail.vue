<template>
  <div class="l_main"
    v-loading.fullscreen.lock="globalLoader">
    <div class="l_wrap">
      <div class="l_contents" :class="show">
        <h1 class="c_h1">センター管理　詳細</h1>
        <div class="l_block">
          <el-form :model="registForm" ref="registForm">
            <table class="c_table_type01">
              <tr>
                <th class="u_ALright">センターID</th>
                <td>{{ registForm.centerid }}</td>
              </tr>
              <tr>
                <th class="u_ALright">センター名</th>
                <td>{{ registForm.centername }}</td>
              </tr>
              <tr>
                <th class="u_ALright">郵便番号</th>
                <td>{{ registForm.zipcode }}</td>
              </tr>
              <tr>
                <th class="u_ALright">住所１（都道府県）</th>
                <td>{{ registForm.address1 }}</span></td>
              </tr>
              <tr>
                <th class="u_ALright">住所２（市区町村）</th>
                <td>{{ registForm.address2 }}</td>
              </tr>
              <tr>
                <th class="u_ALright">住所３（町域）</th>
                <td>{{ registForm.address3 }}</td>
              </tr>
              <tr>
                <th class="u_ALright">住所４</th>
                <td>{{ registForm.address4 }}</td>
              </tr>
              <tr>
                <th class="u_ALright">電話番号</th>
                <td>{{ registForm.tel }}</td>
              </tr>
              <tr>
                <th class="u_ALright">FAX番号</th>
                <td>{{ registForm.fax }} </td>
              </tr>
              <tr>
                <th class="u_ALright">メールアドレス</th>
                <td>{{ registForm.mail }}</td>
              </tr>
              <tr>
                <th class="u_ALright">担当者名</th>
                <td>{{ registForm.stuffname }}</td>
              </tr>
            </table>
          </el-form>
        </div>
        <!-- block end -->
        <div class="l_block">
          <ul class="u_btn_list u_ALcenter">
            <template v-if="acl.update">
              <li><a :href="'/center/edit?id='+registForm.centerid" class="c_btn c_btn_type01 is_blue">更新</a></li>
            </template>
            <li><a href="/center/index" class="c_btn c_btn_type01">戻る</a></li>
          </ul>
        </div>
        <!-- block end -->
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ManagesCenterDetail',
  data () {
    return {
      // 内容を表示OR非表示にする
      show: '',

      // 登録フォーム
      registForm: {
        centerid  : '', // センターID
        centername: '', // センター名
        zipcode   : '', // 郵便番号
        address1  : '', // 住所１（都道府県）
        address2  : '', // 住所２（市区町村）
        address3  : '', // 住所３（町域）
        address4  : '', // 住所４
        tel       : '', // 電話番号
        fax       : '', // FAX番号
        mail      : '', // メールアドレス
        stuffname : '', // 担当者名
        type      : '', // 'update' OR 'register'
      },

      // 権限設定
      acl: {
        update: true, // 更新ボタン
      },
    }
  },
  created() {
    var field = 'id';

    // 現在のURLを取得
    var param = window.location.href.split('/').pop();

    // URLにIDがあるかどうかを確認
    var id  = param.indexOf('?' + field + '=') != -1;

    // URLに番号があるかどうか確認
    var num   = param.match(/\d+$/);

    // id番号がない場合はリダイレクトする
    if (num == null || id == false) {
      window.location.href = '/center/index';
      return;
    }

    var me = this;

    // 内容を表示しない
    me.$data.show = 'display_none';

    // 詳細更新情報
    if (num !== null) {
      // センターIDからデータを取得
      axios.post('/center/detailValue', {num: num[0]}).then(function(response) {
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
          address1  : rec.ADDRESS1,   // 住所１（都道府県）
          address2  : rec.ADDRESS2,   // 住所２（市区町村）
          address3  : rec.ADDRESS3,   // 住所３（町域）
          address4  : rec.ADDRESS4,   // 住所４
          tel       : rec.TEL,        // 電話番号
          fax       : rec.FAX,        // FAX番号
          mail      : rec.MAIL,       // メールアドレス
          stuffname : rec.STUFFNAME,  // 担当者名
          type      : 'update'
        }

        // ローダを表示しない
        me.globalLoader = false;

        // 内容を表示する
        me.$data.show = '';
      });
    }
  },
}
</script>

<style scoped>
.c_table_type01 th {
  vertical-align: middle;
}
.display_none {
  display: none;
}
</style>