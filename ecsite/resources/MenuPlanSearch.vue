<!--
使い方
使い捨て(single use)
parentのcomponentのtemplateに：
<menu-plan-search @setMenuPlan="setMenuPlanInParent" :initialValues="{}" ref="menuPlanSearch" class="wid373"></menu-plan-search>

複数使用(multiple use)
e_keyはloopのindexキーです。
parentのcomponentのtemplateに：
<menu-plan-search @setMenuPlan="setMenuPlanInParent" :initialValues="{key:e_key, sample_object: sample_value}" ref="menuPlanSearch" class="wid373"></menu-plan-search>

そしてsetMenuPlanInParentはメソッドに入れてください。
parentのcomponentのmethodsに：
/**
 * 献立プラン検索データ
 */
setMenuPlanInParent(data) {
  console.log(data);
},

console.log(data)のouputは：
使い捨て：
menuPlanData: {
  MENUTYPEID  : 1, // 献立プランID
  MENUTYPENAME: 'スタンダード和洋朝食', // 献立プラン名
  MENUPROP    : '通常', // 献立属性
  MENUPROPVAL : '0', // 献立属性値
  MENUTYPERANK: 'レギュラー', // 献立プランランク
  initialValues: {}
}

複数使用：
menuPlanData: {
  MENUTYPEID  : 1, // 献立プランID
  MENUTYPENAME: 'スタンダード和洋朝食', // 献立プラン名
  MENUPROP    : '0', // 献立属性
  MENUPROPVAL : '通常', // 献立属性値
  MENUTYPERANK: 'レギュラー', // 献立プランランク
  initialValues: {
    {
      key:11,
      sample_object: 'xxxxx'
    }
  }
}
-->

<template>
  <p class="u_icon_form common_search common_search_menu_plan">
    <button type="button" class="u_icon_side u_icon_nutrition" v-bind:disabled="menuPlanDisable" @click="isVisibleDialog = true"></button>
    <el-form :model="menuPlanSearch" ref="menuPlanSearch">
      <el-form-item class="search_form ext_block">
        <el-input v-model="menuTypeInput" :class="cssTextInput" v-bind:disabled="menuPlanDisable" @blur="changeInputValue" placeholder="献立名称を入力" clearable @clear="clearInputValue"></el-input>
        <input type="hidden" name="MENUTYPEID" value="">
      </el-form-item>
      <el-dialog class="common_search_dialog" title="献立プラン検索" :visible.sync="isVisibleDialog">
        <div class="common_search_header">
          <table class="c_table_type01">
            <tr>
              <th width="300">献立名称</th>
              <td width="300">
                <el-form-item prop="MENUTYPENAME" class="ext_block">
                  <el-input v-model="menuPlanSearch.MENUTYPENAME" clearable></el-input>
                </el-form-item>
              </td>
              <th width="300">献立属性</th>
              <td width="450">
                <div id="app_sel">
                  <el-form-item prop="MENUPROP">
                    <el-radio-group v-model="menuPlanSearch.MENUPROP" @change="changeRadio">
                      <el-radio key="1" label="1" value="1" >全て</el-radio>
                      <el-radio key="2" label="2" value="2" >通常</el-radio>
                      <el-radio key="3" label="3" value="3" >オプション</el-radio>
                    </el-radio-group>
                  </el-form-item>
                </div>
              </td>
            </tr>
            <tr>
              <th width="300">献立プランランク</th>
              <td colspan="4">
                <el-form-item prop="MENUTYPERANK" >
                  <el-checkbox-group v-model="menuPlanSearch.MENUTYPERANK" @change="changeCheckbox">
                    <el-checkbox key="1" label="1" value="1" >スタンダード</el-checkbox>
                    <el-checkbox key="2" label="2" value="2" >ライト</el-checkbox>
                    <el-checkbox key="3" label="3" value="3" >エコノミー</el-checkbox>
                    <el-checkbox key="4" label="4" value="4" >ダブル主菜</el-checkbox>
                    <el-checkbox key="5" label="5" value="5" >副菜３品</el-checkbox>
                  </el-checkbox-group>
                </el-form-item>
              </td>
            </tr>
          </table>
        </div>
        <div class="common_search_buttons">
          <ul class="u_btn_list u_ALcenter">
            <li>
              <a href="javascript:void(0);" class="c_btn c_btn_type01" @click="search" >この条件で検索する</a>
            </li>
            <li><a href="javascript:void(0);" class="c_btn c_btn_type01" @click="clear"
            >入力内容をクリア</a>
            </li>
          </ul>
        </div>
        <div class="common_search_list" :class="tblShow">
          <data-tables-server
            :data="tblData"
            :total="total"
            :table-props="tblProps"
            :page-size="pgsize"
            :current-page="currentPage"
            :pagination-props="{ pageSizes: [10, 20, 50, 100] }"
            @query-change="onLoadData"
            v-loading="this.globalLoader"
            class="list_table"
          >
            <el-table-column label="献立プランID" width="100" align="center" class-name="c_btn_dtb">
              <template slot-scope="scope">
                <template v-if="!multipleSelection">
                  <a href="javascript:void(0);" @click="clickMenuTypeId(scope.row)">{{ scope.row.MENUTYPEID }}</a>
                </template>
                <template v-else>
                  <el-checkbox v-model="scope.row.CHECKED" @change="checkMenuTypeId(scope.row);" :key="scope.row.MENUTYPEID" :value="scope.row.MENUTYPEID">{{ scope.row.MENUTYPEID }}</el-checkbox>
                </template>
              </template>
            </el-table-column>
            <el-table-column
              v-for="item in tblCol"
              align="center"
              :prop="item.prop"
              :label="item.label"
              :key="item.prop">
            </el-table-column>
          </data-tables-server>
        </div>
      </el-dialog>
    </el-form>
  </p>
</template>
<script>
    export default {
      name: 'MenuPlanSearch',
      props: ['initialValues'],
      data() {
        return {
          tblData        : [], // テーブルデータ
          total          : 0, // データ合計
          pgsize         : 10, // ページサイズ
          currentPage    : 1, // 現在のページ
          maindish       : [], // 選択主菜数
          dialogVisible  : false,
          submitLoader   : false,
          tblShow        : "", // テーブルを表示または非表示にする
          isVisibleDialog: false, // モーダル表示切り替え
          menuTypeInput  : '',
          cssTextInput   : 'cs_empty_text',
          sessionName    : 'menu-plan-search-component', // セッション名
          tblCol: [
              {prop: 'MENUTYPENAME', label: '献立名称', align: 'center'},
              {prop: 'MENUPROPVAL', label: '献立属性',  align: 'center'},
              {prop: 'MENUTYPERANK', label: '献立プランランク', align: 'center'},
          ],
          // 検索データ
          menuPlanSearch: {
            MENUTYPENAME : "",  // 献立プラン名
            MENUPROP     : "1", // 献立属性
            MENUTYPERANK : [],  // 献立プランランク
          },
          // テーブルの詳細
          tblProps: {
            border: true,
            stripe: true
          },

          // 献立プランデータデフォルト
          menuTypeDefaultData: {
            MENUTYPEID  : '', // 献立プランID
            MENUTYPENAME: '', // 献立プラン名
            MENUPROP    : '0', // 献立属性
            MENUPROPVAL : '', // 献立属性値
            MENUTYPERANK: '', // 献立プランランク
          },

          // 複数選択時に使用
          selectedMenuTypes: [],    // 選択した献立プランオブジェクト
          multipleSelection: false, // 複数選択のモード切り替え（初期値：単献立プラン選択）

          // 起動ボタンの有効・無効
          menuPlanDisable: false,
        }
      },

      mounted() {
        var me = this;
        me.$data.tblShow = "display_none";

        // set the current page
        var sessionData = this.$session.get(this.sessionName);
        if (sessionData) {
          var queryinfo = JSON.parse(sessionData);
          me.currentPage = queryinfo.page;
          me.pgsize = queryinfo.pageSize;

          // to put the search data on input box if there is session
          me.menuPlanSearch.MENUTYPENAME = queryinfo.MENUTYPENAME;
          me.menuPlanSearch.MENUPROP = queryinfo.MENUPROP;
        }
      },

      methods: {
        /**
         * この条件で検索する
         */
        search() {
          var queryinfo = {
            page: 1,
            pageSize: 10
          };
          this.getData("search", queryinfo);
        },

        /**
         * ページネーション機能
         *
         * @param  object queryinfo ページサイズとページ情報がある
         * @return                  If on the initialization page, then don't display the table else display.
         */
        onLoadData(queryinfo) {
          if (queryinfo.type && queryinfo.type === 'init') {
              return;
          }

          // get session pagination and search information
          var sessionData = this.$session.get(this.sessionName);
          // if no session data then do not display table
          if (typeof sessionData === "undefined") {
            return;
          }

          // parse session data to object
          var param = JSON.parse(sessionData);

          if (param.tblShow !== "display") {
              return;
          } else if (queryinfo.type == "init") {
              queryinfo = param;
          } else {
              // to put on pagination query
              queryinfo.MENUTYPENAME = this.menuPlanSearch.MENUTYPENAME; // 献立プラン名
              queryinfo.MENUPROP = this.menuPlanSearch.MENUPROP; // 献立属性
              queryinfo.MENUTYPERANK = this.menuPlanSearch.MENUTYPERANK; // 献立プランランク
              queryinfo.colSort = queryinfo.sort.prop;
              queryinfo.order = queryinfo.sort.order;
              queryinfo.tblShow = "display";
          }
          // display table on pagination action
          this.getData("pagination", queryinfo);
        },

        /**
         * 検索条件に基づくデータの表示
         *
         * @param  string type      search or pagination
         * @param  object queryinfo ページサイズとページ情報がある
         * @return                  display data on table
         */
        getData(type, queryinfo) {
          // ローダを表示する
          this.globalLoader = true;

          var me = this;
          var param = {};
          if (type == "search") {
            param = {
              MENUTYPENAME: this.menuPlanSearch.MENUTYPENAME, // 献立プラン名
              MENUPROP    : this.menuPlanSearch.MENUPROP, // 献立属性
              MENUTYPERANK: this.menuPlanSearch.MENUTYPERANK, // 献立プランランク
              colSort     : "", // カラムソート
              order       : "", // order - ASC/DESC
              page        : queryinfo.page, // page offset
              pageSize    : queryinfo.pageSize, // page limit
              tblShow     : "display" // use for displaying table after search
            };
            me.currentPage = queryinfo.page;
          } else {
            param = queryinfo;
          }
          // セッションに検索パラメータを保存
          this.$session.start();
          this.$session.set(this.sessionName, JSON.stringify(param));

          // テーブルを表示しない
          me.$data.tblShow = "";

          axios.post("/menutype/search", param).then(function(res) {
            // データテーブルをリフレッシュ
            me.$data.tblData = [];
            me.selectedMenuTypes = [];
            me.menuTypeInput = '';

            // ページネーション
            me.total = res.data.data["total"];

            // データを取得
            var rec = res.data.data["res"];
            var cnt = rec.length;

            // テーブルを表示
            let menuprop = "";
            if (cnt > 0) {
                for (var i = 0; i < cnt; i++) {
                  menuprop = "";
                  if (rec[i].MENUPROP === 0) {
                    switch (rec[i].MENUTYPERANK) {
                      case "1":
                        menuprop = "スタンダード";
                        break;
                      case "2":
                        menuprop = "ライト";
                        break;
                      case "3":
                        menuprop = "エコノミー";
                        break;
                      case "4":
                        menuprop = "ダブル主菜";
                        break;
                      case "5":
                        menuprop = "副菜３品";
                        break;
                      default :
                        menuprop = "";
                        break;
                    }
                  }
                  me.tblData.push({
                    MENUTYPEID  : rec[i].MENUTYPEID, // 献立プランID
                    MENUTYPENAME: rec[i].MENUTYPENAME, // 献立プラン名
                    MENUPROP    : rec[i].MENUPROP, // 献立属性
                    MENUPROPVAL : rec[i].MENUPROP === 0 ? "通常" : "オプション", // 献立属性値
                    MENUTYPERANK: menuprop ? menuprop : "", // 献立プランランク,
                    CHECKED: false,
                  });
                }
            }
            me.currentPage = queryinfo.page;
            me.pgsize = queryinfo.pageSize;
            me.tblShow = "display";
            me.globalLoader = false; // ロードを止める
          });

        },

        /**
         * 入力内容をクリア
         *
         * @return 検索フィールドをリセット
         */
        clear() {
          this.$session.clear(this.sessionName);
          this.menuPlanSearch = {
            MENUTYPENAME: "", // 献立プラン名
            MENUPROP    : "1", // 献立属性
            MENUTYPERANK: [], // 献立プランランク
          };
        },

        /**
         * 献立属性と献立プランランクに矛盾がでないよう制御する
         * 献立プランランクは献立属性が通常の時のみ選択できる
         */
        changeRadio() {
          if(this.menuPlanSearch.MENUPROP !== 2){
            this.menuPlanSearch.MENUTYPERANK = [];
          }
        },

        /**
         * 献立プランランクをクリックした時
         */
        changeCheckbox() {
          if(this.menuPlanSearch.MENUTYPERANK.length >= 1){
            this.menuPlanSearch.MENUPROP = "2";
          }
        },

        /**
         * Direct input
         */
        changeInputValue() {
          this.menuTypeDefaultData.MENUTYPEID = this.menuTypeInput; // 献立プランID
          this.menuTypeDefaultData.MENUTYPENAME = this.menuTypeInput; // 献立プラン名
          this.$emit('setMenuPlan', {'menuPlanData': this.menuTypeDefaultData});
          this.menuPlanSearch.MENUTYPENAME = this.menuTypeInput;
        },

        /**
         * クリアアイコンクリックイベント
         */
        clearInputValue() {
          this.cssTextInput = 'cs_empty_text';
          if(this.multipleSelection === false) { // 単献立プラン選択の場合
            this.$emit('setMenuPlan', {'menuPlanData': this.menuTypeDefaultData});
          }else {
            this.selectedMenuTypes = [];　// 複数献立プラン選択の場合
            this.$emit('setMenuPlan', {'selectedMenuTypes': []});
            for(let o of this.tblData) {
              o.CHECKED = false;
            }
          }
        },

        /**
         * 献立プランIDをクリックした時（単献立プラン選択用）
         * set the menu type plan data
         *
         * @param array data
         */
        clickMenuTypeId(data) {
          // mainly used if component is used for loops
          if (this.initialValues) {
              data.initialValues = this.initialValues;
          }

          // For the parent component
          this.$emit('setMenuPlan', {'menuPlanData': data});
          this.menuTypeInput = data.MENUTYPEID + ':' + data.MENUTYPENAME;
          this.isVisibleDialog = false;
        },

        /**
         * 献立プランIDをチェックした時(複数選択用)
         *
         * @param array data
         */
        checkMenuTypeId(data) {
          // 複数選択の場合
          let isExist = false;
          let me = this;
          let targeti = null;
          // チェック対象データの有無をチェック
          for(let i in me.selectedMenuTypes) {
            if(me.selectedMenuTypes[i].MENUPROP === data.MENUPROP
             && me.selectedMenuTypes[i].MENUTYPEID === data.MENUTYPEID) {
              isExist = true;
              targeti = i;
              break;
            }
          }
          if(!isExist) {
            me.selectedMenuTypes.push(data);          // 追加
          } else if(isExist && targeti !== null) {
            me.selectedMenuTypes.splice(targeti, 1);  // 除外
          }
          // 入力欄の調整
          let inputval = [];
          for(let o of me.selectedMenuTypes) {
            inputval.push(o.MENUTYPENAME);
          }
          me.menuTypeInput = inputval.join(' / ');

          // 呼び出し元での処理を指定
          me.$emit('setMenuPlan', {'selectedMenuTypes': me.selectedMenuTypes});
        },
      },
    };
</script>
<style src="element-ui/../../resources/assets/css/components/items/menutype.css"></style>
<style scoped>
.common_search .el-form {
  width:auto;
}
.el-checkbox-group {
  font-size:1.0em;
}
</style>
