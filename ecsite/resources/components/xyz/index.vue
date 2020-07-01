<template>
<div class="l_main">
  <div class="l_wrap">
    <div class="l_contents"> <!-- InstanceBeginEditable name="contents" -->
      <h1 class="c_h1">受注管理　一覧</h1>
      <div class="l_block">
        <table class="c_table_type01">
          <tr>
            <th>受注番号</th>
            <td><el-input placeholder="Please input" v-model="jobno"></el-input></td>
            <th>請求区分</th>
            <td>
              <el-checkbox-group v-model="billingType">
                <el-checkbox label="0">締請求</el-checkbox>
                <el-checkbox label="1">都度請求</el-checkbox>
                <el-checkbox label="2">代引き</el-checkbox>
              </el-checkbox-group>
            </td>
          </tr>
          <tr>
            <th>受注種別</th>
            <td>
              <el-checkbox-group v-model="orderType">
                <el-checkbox label="0">献⽴受注</el-checkbox>
                <el-checkbox label="1">単品受注</el-checkbox>
              </el-checkbox-group>
            </td>
            <th>受注⽇</th>
            <td>
              <el-date-picker
                v-model="orderdtFrom"
                type="date"
                placeholder="2018/00/00"
                class="dtpickr_width">
              </el-date-picker> ～
              <el-date-picker
                v-model="orderdtTo"
                type="date"
                placeholder="2018/00/00"
                class="dtpickr_width">
              </el-date-picker>
            </td>
          </tr>
          <tr>
            <th>得意先</th>
            <td><p class="u_icon_form">
                <button class="u_icon_side u_icon_customer"></button>
                <input type="text" class="c_tb_type01 u_tb_clear" style="width: 290px;" placeholder="コードもしくは名称を入力">
              </p></td>
            <th>納品予定⽇</th>
            <td><button class="c_btn_calendar"></button>
              <input type="text" class="c_tb_type01" value="2018/00/00" style="width: 110px;">
              ～
              <button class="c_btn_calendar"></button>
              <input type="text" class="c_tb_type01" value="2018/00/00" style="width: 110px;"></td>
          </tr>
          <tr>
            <th>センター</th>
            <td>
              <el-select v-model="value2" placeholder="選択...">
                <el-option
                  v-for="opt in centerList"
                  :key="opt.cd"
                  :label="opt.nm"
                  :value="opt.cd"
                  :disabled="opt.disabled">
                </el-option>
              </el-select>
            </td>
            <th>担当者</th>
            <td><p class="u_icon_form">
                <button class="u_icon_side u_icon_incharge"></button>
                <input type="text" class="c_tb_type01 u_tb_clear" style="width: 290px;">
              </p></td>
          </tr>
          <tr>
            <th>食事使⽤⽇</th>
            <td>
<el-date-picker
  v-model="MENUDATEFROM"
  type="date"
  placeholder="From"
  format="yyyy/MM/dd">
</el-date-picker>
<!--
              <button class="c_btn_calendar"></button>
              <input type="text" class="c_tb_type01" value="2018/00/00" style="width: 110px;">
-->
              ～
<el-date-picker
  v-model="MENUDATETO"
  type="date"
  placeholder="To"
  format="yyyy/MM/dd">
</el-date-picker>
</td>
            <th>受注ステータス</th>
            <td><ul class="u_cb_list">
                <li>
                  <input type="checkbox" class="c_cb_input" id="checkbox06">
                  <label class="c_cb_type01" for="checkbox06">受注未確定</label>
                </li>
                <li>
                  <input type="checkbox" class="c_cb_input" id="checkbox07">
                  <label class="c_cb_type01" for="checkbox07">受注確定</label>
                </li>
                <li>
                  <input type="checkbox" class="c_cb_input" id="checkbox08">
                  <label class="c_cb_type01" for="checkbox08">売上確定</label>
                </li>
              </ul></td>
          </tr>
          <tr>
            <th>商品</th>
            <td><p class="u_icon_form">
                <button class="u_icon_side u_icon_product"></button>
                <input type="text" class="c_tb_type01 u_tb_clear" style="width: 290px;">
              </p></td>
            <th>朝/昼/夕</th>
            <td><ul class="u_cb_list">
                <li>
                  <input type="checkbox" class="c_cb_input" id="checkbox09">
                  <label class="c_cb_type01" for="checkbox09">朝食</label>
                </li>
                <li>
                  <input type="checkbox" class="c_cb_input" id="checkbox10">
                  <label class="c_cb_type01" for="checkbox10">昼食</label>
                </li>
                <li>
                  <input type="checkbox" class="c_cb_input" id="checkbox11">
                  <label class="c_cb_type01" for="checkbox11">夕食</label>
                </li>
              </ul></td>
          </tr>
          <tr>
            <th>請求先</th>
            <td><p class="u_icon_form">
                <button class="u_icon_side u_icon_billing"></button>
                <input type="text" class="c_tb_type01 u_tb_clear" style="width: 290px;">
              </p></td>
            <th class="has_border_btm">注⽂⽅法</th>
            <td class="has_border_btm"><ul class="u_cb_list">
                <li>
                  <input type="checkbox" class="c_cb_input" id="checkbox12">
                  <label class="c_cb_type01" for="checkbox12">自動</label>
                </li>
                <li>
                  <input type="checkbox" class="c_cb_input" id="checkbox13">
                  <label class="c_cb_type01" for="checkbox13">都度</label>
                </li>
              </ul></td>
          </tr>
          <tr>
            <th>献立</th>
            <td><select class="c_selectbox">
                <option value="0" selected="selected">選択...</option>
                <option value="1">テキストテキスト</option>
                <option value="2">テキストテキスト</option>
              </select></td>
          </tr>
        </table>
      </div>
      <!-- block end -->
      <div class="l_block">
        <ul class="u_btn_list u_ALcenter">
          <li><a href="#" class="c_btn c_btn_type01" v-on:click="search">この条件で検索</a></li>
          <li><a href="#" class="c_btn c_btn_type01" v-on:click="clear">初期値に戻す</a></li>
          <li><a href="#" class="c_btn c_btn_type01 is_blue">CSVエクスポート</a></li>
          <li><a href="#" class="c_btn c_btn_type01 is_orange">受注確定</a></li>
          <li><a href="#" class="c_btn c_btn_type01 is_orange">売上確定</a></li>
          <li><a href="#" class="c_btn c_btn_type01 is_blue">新規登録</a></li>
          <li><a href="#" class="c_btn c_btn_type01 is_blue">商品の一括差替</a></li>
        </ul>
      </div>
      <!-- block end -->
      <div class="l_block">
        <table class="u_datatables c_table_type02">
          <thead>
            <tr>
              <th>更新</th>
              <th>受注番号</th>
              <th>請求区分</th>
              <th>受注種別</th>
              <th>受注⽇</th>
              <th>得意先</th>
              <th>センター</th>
              <th>担当者名</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><p class="c_btn_dtb">
                  <button class="c_btn c_btn_type01 is_blue c_modal_link">更新</button>
                </p></td>
              <td><a href="#">00012345</a></td>
              <td>締請求</td>
              <td>献⽴受注</td>
              <td>2018/00/00</td>
              <td>000987: 得意先名称です。</td>
              <td>000123: 関通（低温）</td>
              <td>担当者名です</td>
            </tr>
            <tr>
              <td><p class="c_btn_dtb">
                  <button class="c_btn c_btn_type01 is_blue c_modal_link">更新</button>
                </p></td>
              <td><a href="#">00054321</a></td>
              <td>締請求</td>
              <td>献⽴受注</td>
              <td>2018/00/00</td>
              <td>000987: 得意先名称です。</td>
              <td>000123: 関通（低温）</td>
              <td>担当者名です</td>
            </tr>
            <tr class="u_data_change">
              <td><p class="c_btn_dtb">
                  <button class="c_btn c_btn_type01 is_blue c_modal_link">更新</button>
                </p></td>
              <td><a href="#">00023541</a></td>
              <td>締請求</td>
              <td>献⽴受注</td>
              <td>2018/00/00</td>
              <td>000987: 得意先名称です。</td>
              <td>000123: 関通（低温）</td>
              <td>担当者名です</td>
            </tr>
            <tr>
              <td><p class="c_btn_dtb">
                  <button class="c_btn c_btn_type01 is_blue c_modal_link">更新</button>
                </p></td>
              <td><a href="#">00032145</a></td>
              <td>締請求</td>
              <td>献⽴受注</td>
              <td>2018/00/00</td>
              <td>000987: 得意先名称です。</td>
              <td>000123: 関通（低温）</td>
              <td>担当者名です</td>
            </tr>
            <tr>
              <td><p class="c_btn_dtb">
                  <button class="c_btn c_btn_type01 is_blue c_modal_link">更新</button>
                </p></td>
              <td><a href="#">00014523</a></td>
              <td>締請求</td>
              <td>献⽴受注</td>
              <td>2018/00/00</td>
              <td>000987: 得意先名称です。</td>
              <td>000123: 関通（低温）</td>
              <td>担当者名です</td>
            </tr>
          </tbody>
        </table>
        <div class="c_modal_box">
          <div class="c_modal_inner"><span class="c_btn_close"><img src="/common/images/icons/icon_modal_close.png" alt="close"></span>
            <p class="c_modal_ttl">受注番号：3456789　明細番号：1234　2018/00/00（日）朝⾷</p>
            <div class="c_modal_cont">
              <table class="c_table_type01 u_mab30">
                <tr>
                  <th>得意先</th>
                  <td colspan="2">12345678</td>
                </tr>
                <tr>
                  <th>献⽴</th>
                  <td colspan="2">C3⾊R</td>
                </tr>
                <tr>
                  <th>受注単価／数量（人前）</th>
                  <td class="has_b_dash_right" width="340"><input type="text" class="c_tb_type01" style="width: 340px;"></td>
                  <td><input type="text" class="c_tb_type01" style="width: 140px;"></td>
                </tr>
                <tr>
                  <th>原価計／受注単価計</th>
                  <td colspan="2">999,999.00 円 / 999,999.00 円</td>
                </tr>
                <tr>
                  <th>納品予定⽇</th>
                  <td colspan="2"><button class="c_btn_calendar"></button>
                    <input type="text" class="c_tb_type01" value="2018/00/00" style="width: 295px;"></td>
                </tr>
                <tr>
                  <th>備考</th>
                  <td colspan="2"><input type="text" class="c_tb_type01" value="明細⾏単位の備考です。" style="width: 510px;"></td>
                </tr>
              </table>
              <button class="c_btn c_btn_type01 u_Mcenter" style="width: 160px;">入力内容を反映</button>
            </div>
          </div>
        </div>
      </div>
      <!-- block end -->
      <!-- InstanceEndEditable --> </div>
    <!-- contents end -->
  </div>
  <!-- wrap end -->
</div>
<!-- main end -->
</template>
<style>
.dtpickr_width {
  width: 140px !important;
}

.el-picker-panel__icon-btn {
  padding: 0px 5px;
}
</style>
<script>
    export default {
        /**
         * 通信で受け取るデータとかを定義しときます
         */
        data: function () {
            return {
                centerList  : [],
                sliderVal   : 50,
                options2    : [{
                  value: 'Option1',
                  label: 'Option1'
                }, {
                  value: 'Option2',
                  label: 'Option2',
                  disabled: true
                }, {
                  value: 'Option3',
                  label: 'Option3'
                }, {
                  value: 'Option4',
                  label: 'Option4'
                }, {
                  value: 'Option5',
                  label: 'Option5'
                }],
                value2      : '',
                MENUDATEFROM: '',
                MENUDATETO  : '',
                jobno       : '',
                billingType : [],
                orderType   : [],
                orderdtFrom : '',
                orderdtTo   : '',
          }
        },
/*
        dtvalue: () => ({
            columns: [
                { title: 'User ID', field: 'uid', sortable: true },
                { title: 'Username', field: 'name' },
                { title: 'Age', field: 'age', sortable: true },
                { title: 'Email', field: 'email' },
                { title: 'Country', field: 'country' }
            ],
            data: [],
            total: 0,
            query: {}
        }),
*/
        /**
         * 初期処理
         *
         * インスタンス生成時
         */
        created: function () {
            console.log('[created] xyz-index');
        },

        /**
         * 初期処理
         *
         * インスタンスがマウントされた後に呼ばれる
         *
         * @see https://jp.vuejs.org/v2/api/#mounted
         */
        mounted: function () {
            console.log('[mounted] xyz-index');

            // DOMに反映されたあと
            this.$nextTick();

            // 初期データを取得しに行く
            // 自分（ViewComponent）のスコープを渡さないと、axiosのcallbackで参照出来ないのでパラメータにthisをセットしてます
            this.init(this);

            // ここからmethodに定義したsearchを呼びたいとき
            this.search();
        },
        /**
         * ここに実処理をワラワラと書きます
         */
        methods: {
            /**
             * 画面描画後の初期通信
             */
            init: function (self) {

                // ローダー出したりとかしたほうがいいね

                // 通信実行
                axios.get('/xyz/init-index').then(function(res) {
                    var values = res.data;
                    if (values.st === 'ng') {
                        console.log('なんかえらー');
                        return;
                    }
                    // 選択肢の値をセットしまーす
                    self.centerList = values.data.center;
                });
            },
            /**
             * 適当に作ったお試しです
             */
            search: function(e){
                console.log('search!!');
            },
            clear: function(e){
                console.log('clear!!');
            }
        }
    }
</script>
