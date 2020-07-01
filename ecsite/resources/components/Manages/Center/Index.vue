<template>
  <div class="l_main">
    <div class="l_wrap">
      <div class="l_contents">
        <h1 class="c_h1">センター管理　一覧</h1>
        <div class="">
          <div class="l_block">
            <el-form :model="search" ref="search">
              <table class="c_table_type01">
                <tr>
                  <th>センターID</th>
                  <td>
                    <el-form-item class="ext_block">
                      <el-input v-model="search.centerid"></el-input>
                    </el-form-item>
                  </td>
                  <th>センター名</th>
                  <td>
                    <el-form-item class="ext_block">
                      <el-input v-model="search.centername"></el-input>
                    </el-form-item>
                  </td>
                </tr>
                <tr>
                  <th>住所１（都道府県）</th>
                  <td>
                    <el-form-item class="ext_block">
                      <el-select clearable v-model="search.address1" placeholder="選択してください">
                        <el-option
                          v-for="item in prefecture"
                          :key="item.CODE"
                          :label="item.LOCALGOVERNMENTNAME"
                          :value="item.CODE">
                        </el-option>
                      </el-select>
                    </el-form-item>
                  </td>
                  <th class="has_border_btm">住所２（市区町村）</th>
                  <td class="has_border_btm">
                    <el-form-item class="ext_block">
                      <el-input v-model="search.address2"></el-input>
                    </el-form-item>
                  </td>
                </tr>
                <tr class="custom_border">
                  <th>代表電話番号</th>
                  <td>
                    <el-form-item class="ext_block">
                      <el-input v-model="search.tel"></el-input>
                    </el-form-item>
                  </td>
                </tr>
              </table>
            </el-form>
          </div>
          <!-- block end -->
          <div class="l_block">
            <ul class="u_btn_list u_ALcenter">
              <li>
                <a href="javascript:void(0);" class="c_btn c_btn_type01" @click="onSearch">この条件で検索する</a>
              </li>
              <li>
                <a href="javascript:void(0);" class="c_btn c_btn_type01" @click="onClear">初期値に戻す</a>
              </li>
              <template v-if="acl.add">
                <li><a href="/center/edit" class="c_btn c_btn_type01 is_blue">新規登録</a></li>
              </template>
            </ul>
          </div>
          <!-- block end -->
          <div class="l_block"
            :class="tblShow">
            <data-tables-server
              :data="tblData"
              :total="total"
              :table-props="tblProps"
              :page-size="pgsize"
              :current-page="currentPage"
              :pagination-props="{ pageSizes: [10, 20, 50, 100] }"
              @query-change="onLoadData"
              v-loading="this.globalLoader"
              class="list_table">
              <template v-if="acl.edit">
                <el-table-column label="更新" width="85" align="center" class-name="c_btn_dtb">
                  <template slot-scope="scope">
                    <form ref="update" action="/center/edit">
                      <input type="hidden" name="id" :value="scope.row.centerid">
                      <button class="c_btn c_btn_type01 is_blue c_modal_link">更新</button>
                    </form>
                  </template>
                </el-table-column>
              </template>
              <el-table-column
                label="センター<br>ID"
                width="95"
                align="center"
                class-name="c_btn_dtb"
                sortable
                prop="centerid"
                :render-header="renderHeader">
                <template slot-scope="scope">
                    <a :href="'/center/detail?id='+scope.row.centerid">{{ scope.row.centerid }}</a>
                </template>
              </el-table-column>
              <el-table-column
                v-for="item in tblHdr"
                :align="item.align"
                :prop="item.prop"
                :label="item.label"
                :width="item.width"
                :key="item.prop"
                :render-header="renderHeader"
                sortable="custom">
              </el-table-column>
            </data-tables-server>
          </div>
          <!-- block end -->
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  name: 'ManagesCenterIndex',
  data () {
    var tblHdr = [{
        prop: 'centername',
        label: 'センター名',
        width: '125',
        align: 'center'
      }, {
        prop: 'address1',
        label: '住所１<br>（都道府県）',
        width: '120',
        align: 'center'
      }, {
        prop: 'address2',
        label: '住所２<br>（市区町村）',
        width: '130',
        align: 'center'
      }, {
        prop: 'tel',
        label: '電話番号',
        width: '125',
        align: 'center'
      }, {
        prop: 'fax',
        label: 'FAX番号',
        width: '125',
        align: 'center'
      }, {
        prop: 'mail',
        label: 'メールアドレス',
        width: '',
        align: 'center'
      }, {
        prop: 'stuffname',
        label: '担当者名',
        width: '110',
        align: 'center'
    }];
    return {
      // テーブルヘッダ
      tblHdr,

      // テーブルデータ
      tblData: [],

      // 都道府県
      prefecture: [],

      // データ合計
      total: 0,

      // ページサイズ
      pgsize: 10,

      // 現在のページ
      currentPage: 1,

      // テーブルを表示または非表示にする
      tblShow: '',

      // 検索データ
      search: {
        centerid  : '', // センターID
        centername: '', // センター名
        address1  : '', // 住所１（都道府県）
        address2  : '', // 住所２（市区町村）
        tel       : ''  // 代表電話番号
      },

      // テーブルの詳細
      tblProps: {
        border: true,
        stripe: true,
        defaultSort: {
          prop: '',
          order: ''
        }
      },

      // 権限設定
      acl: {
        add:  true, // 新規登録ボタン
        edit: true, // 更新ボタン
      },
    }
  },
  created() {
    var me = this;
    me.$data.tblShow = 'display_none';

    // 現在のページを設定する
    var sessionData = this.$session.get('center-index');
    if (typeof sessionData !== 'undefined') {
      var queryinfo = JSON.parse(sessionData);
      this.$data.currentPage = queryinfo.OFFSET;
      this.$data.pgsize      = queryinfo.LIMIT;
      this.$data.tblProps.defaultSort.prop  = queryinfo.colSort;
      this.$data.tblProps.defaultSort.order = queryinfo.order;

      // セッションがあったら入力のボックスに検索データをおく
      Object.assign(this.$data.search, queryinfo);
    }

    // 住所１（都道府県）
    axios.get('/common/getprefecture').then(function(res) {
      me.prefecture = res.data;
    });
  },
  methods: {
    /**
     * テーブルヘッダーでhtmlを使うことができる。
     *
     * @param  string h              html プロパティ ex. span or div
     * @param  object options.column カラムデータ
     * @param  int    options.$index カラム順番
     * @return string                新しいラベル
     */
    renderHeader(h, {column, $index}) {
      return h('span', {
        attrs: {
          class: ''
        },
        domProps: {
          innerHTML: column.label
        }
      });
    },

    /**
     * 初期値に戻す
     *
     * @return 検索フィールドをリセット
     */
    onClear() {
      for (var i in this.search) {
        this.search[i] = '';
      }
    },

    /**
     * ページネーション機能
     *
     * @param  object queryinfo ページサイズとページ情報がある
     */
    onLoadData(queryinfo) {
      // セッションデータ「ページネーションと検索入力データ」を取得
      var sessionData = this.$session.get('center-index');

      // セッションデータがない場合は、テーブルにデータを表示しません。
      if (typeof sessionData === 'undefined') {
        return;
      }

      // セッションデータをオブジェクトにparseする
      var param = JSON.parse(sessionData);

      if (param.tblShow !== 'display') {
        return;
      } else if (queryinfo.type == 'init') {
        queryinfo = param;
      } else {
        // ページネーションクエリを設定する
        queryinfo.centerid   = this.search.centerid;   // センターID
        queryinfo.centername = this.search.centername; // センター名
        queryinfo.address1   = this.search.address1;   // 住所１（都道府県）
        queryinfo.address2   = this.search.address2;   // 住所２（市区町村）
        queryinfo.tel        = this.search.tel;        // 代表電話番号
        queryinfo.colSort    = queryinfo.sort.prop;    // ソートする列
        queryinfo.order      = queryinfo.sort.order;   // ソートオーダー
        queryinfo.tblShow    = 'display';
        queryinfo.LIMIT      = queryinfo.pageSize;
        queryinfo.OFFSET     = queryinfo.page;
      }

      // テーブルにデータを表示される
      this.getData('pagination', queryinfo);
    },

    /**
     * この条件で検索する
     */
    onSearch() {
      this.getData('search');
    },

    /**
     * データを検索してテーブルに表示される.
     * onSearch()とonLoadData(pagination)機能を使います。
     *
     * @param  string type      'search' OR 'pagination'
     * @param  object queryinfo ページサイズとページ情報がある
     */
    getData(type, queryinfo) {
      var me = this;
      var param = {};

      if (type == 'search') {
        param         = this.search;        // 検索入力データ
        param.colSort = this.$data.tblProps.defaultSort.prop;  // カラムソート
        param.order   = this.$data.tblProps.defaultSort.order; // order - ASC/DESC
        param.OFFSET  = this.$data.currentPage;                // ページのOFFSET
        param.LIMIT   = this.$data.pgsize;                     // ページのLIMIT
        param.tblShow = 'display';          // 検索後のテーブルの表示に使用
      } else {
        param = queryinfo;
      }

      // セッションストレージに入力データとページネーションのデータを格納する
      this.$session.start();
      this.$session.set('center-index', JSON.stringify(param));

      // loaderを表示
      this.globalLoader = true;

      // テーブルを表示しない
      me.$data.tblShow = '';

      axios.post('/center/search', param).then(function(res) {
        // データテーブルをリフレッシュ
        me.$data.tblData = [];

        // ページネーション
        me.total = res.data.data[0]['total'];

        // データを取得
        var rec = res.data.data[0]['rec'];
        var cnt = rec.length;

        if (cnt > 0) {
          for (var i = 0; i < cnt; i++) {
            me.$data.tblData.push({
              centerid  : rec[i].CENTERID,   // センターID
              centername: rec[i].CENTERNAME, // センター名
              address1  : rec[i].ADDRESS1,   // 住所１（都道府県）
              address2  : rec[i].ADDRESS2,   // 住所２（市区町村）
              tel       : rec[i].TEL,        // 代表電話番号
              fax       : rec[i].FAX,        // FAX番号
              mail      : rec[i].MAIL,       // メールアドレス
              stuffname : rec[i].STUFFNAME,  // 担当者名
            });
          }
        }

        // loader停止
        me.globalLoader = false;

        me.$data.currentPage = param.OFFSET;
        me.$data.pgsize      = param.LIMIT;
        me.$data.tblProps.defaultSort.prop  = param.colSort;
        me.$data.tblProps.defaultSort.order = param.order;
      });
    }
  }
}
</script>
