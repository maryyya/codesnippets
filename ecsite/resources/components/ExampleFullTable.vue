<template>
  <div class="l_main">
    <div class="l_wrap">
      <div class="l_contents">
        <h1 class="c_h1">サンプル</h1>
        <div class="l_block">
          <el-form ref="formData">
            <table class="c_table_type01">
              <thead>
                <tr>
                  <template v-for="(itm, index) in tblHeader">
                    <th v-if="index === 'CHECKBOXPROP'" v-bind:style="{ width: itm.width + 'px' }"><el-checkbox v-model="tblHeader.CHECKBOXPROP.val" @change="checkAll"></el-checkbox></th>
                    <th v-else v-bind:style="{ width: itm.width + 'px' }">{{ itm.label }}</th>
                  </template>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(itm, key) in tblData">
                  <td class="c_btn_dtb">
                    <template v-if="itm.FORMVALUE.edit">
                      <button type="button" class="c_btn c_btn_type01 is_blue" @click="updateCol(itm)">更新</button>
                      <button type="button" class="c_btn c_btn_type01" @click="cancelCol(itm)">キャンセル</button>
                    </template>
                    <template v-else>
                      <button type="button" class="c_btn c_btn_type01 is_blue" @click="itm.FORMVALUE.edit=true">編集</button>
                      <button type="button" class="c_btn c_btn_type01 is_orange" @click="deleteCol(itm, key)">削除</button>
                    </template>
                  </td>
                  <td><el-checkbox v-model="itm.FORMVALUE.CHECKBOXPROP" @change="checkEach"></el-checkbox></td>
                  <td>{{ itm.FORMVALUE.SHIPMENTNUMBER }}</td>
                  <td>
                    <el-form-item v-if="itm.FORMVALUE.edit" class="ext_block">
                      <el-input placeholder="センター" v-model="itm.FORMVALUE.CENTER"clearable></el-input>
                    </el-form-item>
                    <span v-else>{{ itm.FORMVALUE.CENTER }}</span>
                  </td>
                  <td>
                    <el-form-item v-if="itm.FORMVALUE.edit" class="ext_block">
                      <el-input placeholder="得意先" v-model="itm.FORMVALUE.CUSTOMER" clearable></el-input>
                    </el-form-item>
                    <span v-else>{{ itm.FORMVALUE.CUSTOMER }}</span>
                  </td>
                  <td>
                    <el-form-item v-if="itm.FORMVALUE.edit" class="ext_block">
                      <el-input placeholder="商品名" v-model="itm.FORMVALUE.PRODUCTNAME" clearable></el-input>
                    </el-form-item>
                    <span v-else>{{ itm.FORMVALUE.PRODUCTNAME }}</span>
                  </td>
                  <td>
                    <el-form-item v-if="itm.FORMVALUE.edit" class="ext_block">
                      <el-input placeholder="食数" v-model="itm.FORMVALUE.MEALCOUNT" clearable></el-input>
                    </el-form-item>
                    <span v-else>{{ itm.FORMVALUE.MEALCOUNT }}</span>
                  </td>
                  <td>
                    <el-form-item v-if="itm.FORMVALUE.edit" class="ext_block">
                      <el-input placeholder="パック数" v-model="itm.FORMVALUE.PACKCOUNT" clearable></el-input>
                    </el-form-item>
                    <span v-else>{{ itm.FORMVALUE.PACKCOUNT }}</span>
                  </td>
                  <td>
                    <el-form-item v-if="itm.FORMVALUE.edit" class="ext_block">
                      <el-input placeholder="ロス" v-model="itm.FORMVALUE.LOSS" clearable></el-input>
                    </el-form-item>
                    <span v-else>{{ itm.FORMVALUE.LOSS }}</span>
                  </td>
                  <td>
                    <el-form-item v-if="itm.FORMVALUE.edit" class="ext_block">
                      <el-input placeholder="献立名" v-model="itm.FORMVALUE.MENUNAME" clearable></el-input>
                    </el-form-item>
                    <span v-else>{{ itm.FORMVALUE.MENUNAME }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </el-form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'ExampleFullTable',
    data() {
      return {
        tblData: [],
        tblHeader: {
          BUTTONS       : {label: '', width: '350'}, // 編集・更新・削除・キャンセルのボタン
          CHECKBOXPROP  : {label: '', width: '100', val: false}, // チェックボックスのため
          SHIPMENTNUMBER: {label: '出荷番号'},
          CENTER        : {label: 'センター'},
          CUSTOMER      : {label: '得意先'},
          PRODUCT       : {label: '商品名'},
          MEALCOUNT     : {label: '食数'},
          PACKCOUNT     : {label: 'パック数'},
          LOSS          : {label: 'ロス'},
          MENUNAME      : {label: '献立名'},
        },
      }
    },

    mounted() {
      let padToFour = number => number <= 9999 ? `000${number}`.slice(-4) : number;
      let total = 1000;
      for (let i=1; i<=total; i++) {
        let baseData = {};
        let getData = {
          CHECKBOXPROP  : false,
          SHIPMENTNUMBER: padToFour(i),
          CENTER        : 'センター' + i,
          CUSTOMER      : '得意先' + i,
          PRODUCTNAME   : '商品名' + i,
          MEALCOUNT     : '食数' + i,
          PACKCOUNT     : 'パック数' + i,
          LOSS          : 'ロス' + i,
          MENUNAME      : '献立名' + i,
          edit: false,
        };
        // フォームデータのため
        baseData.FORMVALUE = Object.assign({}, getData);

        // これはユーザーがキャンセルボタンをクリックしたときに使用
        baseData.DEFAULTVALUE = Object.assign({}, getData);

        this.tblData.push(baseData);
      }
    } ,

    methods: {
      /**
       * 更新ボタンをクリックしたとき
       *
       * @param object itm 行データ
       */
      updateCol(itm) {
        this.$confirm('更新します。よろしいですか？', '確認', {
          confirmButtonText: '更新',
          cancelButtonText: 'キャンセル',
          type: 'warning'
        }).then(() => {
          itm.DEFAULTVALUE = Object.assign({}, itm.FORMVALUE);
          itm.FORMVALUE.edit = false;
          this.$message({
            type: 'success',
            message: '更新しました。',
          });
        }).catch(() => {});
      },

      /**
       * キャンセルボタンをクリックしたとき
       *
       * @param object itm 行データ
       */
      cancelCol(itm) {
        itm.FORMVALUE = Object.assign({}, itm.DEFAULTVALUE);
        itm.FORMVALUE.edit = false;
      },

      /**
       * 削除ボタンをクリックしたとき
       *
       * @param object itm 行データ
       * @param int    key 行キー
       */
      deleteCol(itm, key) {
        this.$confirm('削除します。よろしいですか？', '確認', {
            confirmButtonText: '削除',
            cancelButtonText: 'キャンセル',
            type: 'warning'
        }).then(() => {
          this.tblData.splice(key, 1);
          this.$message({
            type: 'success',
            message: '削除しました。',
          });
        }).catch(() => {});
      },

      /**
       * すべてのデータをチェック
       *
       * @param boolean val
       */
      checkAll(val) {
        let msg = val?'全チェックします':'全チェックを外します';
        this.$confirm(msg+'。よろしいですか？', '確認', {
          confirmButtonText: 'はい',
          cancelButtonText: 'キャンセル',
          type: 'warning'
        }).then(() => {
          this.tblData.map((itm) => {
            itm.FORMVALUE.CHECKBOXPROP = val;
            itm.DEFAULTVALUE.CHECKBOXPROP = val;
          }, val);
        }).catch(() => {
          let checkedAll = this.tblData.every((itm) => {
              return itm.FORMVALUE.CHECKBOXPROP;
          });
          this.tblHeader.CHECKBOXPROP.val = !val && checkedAll;
        });
      },

      /**
       * 行チェックボックスがチェックされているとき
       *
       * @param boolean val
       */
      checkEach(val) {
        if (!val) {
          this.tblHeader.CHECKBOXPROP.val = val;
        } else {
          let checkedAll = this.tblData.every((itm) => {
            return itm.FORMVALUE.CHECKBOXPROP;
          });
          this.tblHeader.CHECKBOXPROP.val = checkedAll;
        }
      },
    },
  }
</script>
<style scoped>
  .c_table_type01 tr td:first-child {
    border-left: 1px solid #cccccc;
  }
  .c_table_type01 tr th,
  .c_table_type01 tr td {
    text-align: center;
    border-right: 1px solid #ebeef5;
  }

  .c_table_type01 .c_btn_dtb button {
    display: inline-block;
  }

  .c_table_type01 thead tr th {
    height: 55px;
  }

  .c_table_type01 tr:hover {
    background-color: #f5fdf8 !important;
    cursor: pointer !important;
  }

  .c_table_type01 tr:nth-child(even) {
    background-color: #f2f2f2;
  }
</style>
