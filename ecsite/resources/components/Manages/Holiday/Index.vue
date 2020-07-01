<template>
  <div class="l_main">
    <div class="l_wrap">
      <div class="l_contents">
        <h1 class="c_h1">休業日管理</h1>
        <div class="">
          <div class="l_block">
            <ul class="u_btn_list u_ALcenter">
              <li>
                <el-select clearable v-model="selectedyear" placeholder="選択してください">
                  <el-option
                  v-for="item in targetyear"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
                  </el-option>
                </el-select>
              </li>
              <li>
                <a href="javascript:void(0);" class="c_btn c_btn_type01" @click="onView('表示', 'all')">表示</a>
              </li>
              <template v-if="acl.holiday">
                <li>
                  <a href="javascript:void(0);" class="c_btn c_btn_type01 is_blue" @click="onView('休業日設定', 'holiday')">休業日設定</a>
                </li>
              </template>
              <template v-if="acl.publicHoliday">
                <li>
                  <a href="javascript:void(0);" class="c_btn c_btn_type01 is_blue" @click="onView('祝祭日設定', 'publicHoliday')">祝祭日設定</a>
                </li>
              </template>
            </ul>
          </div>
          <!-- block end -->
          <div class="block u_mab30" v-loading="globalLoader">
            <h2 :class="showH2">{{ blockTitle }}</h2>
            <div :class="'yr'+selectedyear+' calendar-block u_ALcenter'">
              <table class="calendarTbl" v-for="(month, index) in tblData">
                <thead><tr><th colspan="7">{{ index }}</th></tr></thead>
                <thead>
                  <tr>
                    <th>日</th>
                    <th>月</th>
                    <th>火</th>
                    <th>水</th>
                    <th>木</th>
                    <th>金</th>
                    <th>土</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="week in month">
                    <td v-for="item in week" :class="item.day=='sat'||item.day=='sun'?blockType+'weekend':blockType+'weekday'">
                      <template v-if="item.dayOfMonth">
                        <template v-if="item.type!=='all'">
                          <a
                            :class="item.status"
                            href="javascript:void(0);"
                            :data-flg="blockType=='publicHoliday'?item.pubholflg:item.holflg"
                            v-on:click="onChangeData($event, item.date, blockType)"
                          >{{ item.dayOfMonth }}
                          </a>
                        </template>
                        <template v-else>
                          <span
                            :class="item.status=='publicHoliday'?'hyojiPublicHoliday':item.status=='normal'?'':item.status"
                          >{{ item.dayOfMonth }}
                          </span>
                        </template>
                      </template>
                    </td>
                  </tr>
                  <tr v-if="month.length < emptyRowsCnt">
                    <td v-for="item in (emptyRowsCnt+1)" class="emptyRowCol"></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <ul class="u_btn_list u_ALcenter">
              <template v-if="acl.regist">
                <li :class="dispBtn">
                  <a href="javascript:void(0);" class="c_btn c_btn_type01 is_orange" @click="onRegist();">登録</a>
                </li>
              </template>
            </ul>
          </div>
          <!-- block end -->
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  name: 'ManagesHolidayIndex',
  data () {
    return {
      // テーブルデータ
      tblData: [],

      // 対象年
      targetyear: [],

      // 選択した年
      selectedyear: '',

      // 登録ボタンを表示する
      dispBtn: 'hide',

      // カレンダーブロックのタイトル
      blockTitle: '',

      // カレンダーブロックのタイプ「holiday, publicHoliday」
      blockType: '',

      // 登録するのデータ
      registData: [],

      // H2
      showH2: 'hide',

      // 空の行数の場合
      emptyRowsCnt: 6,

      // 権限設定
      acl: {
        holiday      : true, // 休業日設定
        publicHoliday: true, // 祝祭日設定
        regist       : true, // 登録
      },
    };
  },
  created() {
    var me = this;

    // 対象年
    axios.get('/holiday/year').then(function(res) {
      me.targetyear = res.data;
    });
  },
  methods: {
    /**
     * 検索した、カレンダーを表示される

     * @param  string type 'publicHoliday' OR 'holiday'
     */
    onView(name, type) {
      var me = this;

      me.$data.tblData    = [];
      me.$data.dispBtn    = 'hide';
      me.$data.registData = [];

      if (me.selectedyear < 1) {
        return;
      }

      var param = {
        btnType: {name:name, type: type},
        year   : me.selectedyear
      }

      // ローダを表示する
      this.globalLoader = true;

      axios.post('/holiday/edit', param).then(function(res) {
        var data = res.data.data;
        var rec = data.rec;

         me.$data.tblData = rec;    // テーブルデータ
         me.$data.showH2  = 'c_h2'; // H2
         me.$data.globalLoader = false;  // ロードを止まれる
         me.$data.dispBtn    = data.btnType.type =='all'?'hide':''; // 登録ボタンを表示する
         me.$data.blockTitle = data.btnType.name; // カレンダーブロックのタイトル
         me.$data.blockType  = data.btnType.type; // カレンダーブロックのタイプ「holiday, publicHoliday」
      });
    },

    /**
     * 休日を変更
     *
     * @param  object event     onclickのイベント
     * @param  string date      日付
     * @param  string blockType 祝祭日の休業日「publicHoliday」, 通常の休業日「holiday」, 通常の営業日「normal」
     */
    onChangeData(event, date, blockType) {
      var me         = this;
      var toggle     = '';
      var className  = event.target.className;
      var flg        = event.target.dataset.flg;

      switch(blockType) {
        // 休業日設定
        case 'holiday':
          switch(className) {
            // 祝祭日の休業日
            case 'publicHoliday':
              toggle = 'holidayBusinessday';
              break;

            // 祝祭日の営業日
            case 'holidayBusinessday':
              toggle = 'publicHoliday';
              break;

            // 通常の営業日
            case 'normal':
              toggle = 'holiday';
              break;

            // 通常の休業日
            default:
              toggle = 'normal';
          }
          break;

        // 祝祭日設定
        case 'publicHoliday':
          switch(className) {
            // 通常の営業日
            case 'normal':
              toggle = 'publicHoliday';
              break;

            // 通常の休業日
            case 'holiday':
              toggle = 'publicHoliday';
              break;

            // 祝祭日の休業日と祝祭日の営業日
            default:
              toggle = 'normal';
          }
          break;

        default:
        toggle = 'normal';
      }

      // クラスを変更
      event.target.className = toggle;

      // registDataにデータを追加する
      me.registData.push({
        date    : date,
        changeTo: blockType,
        value   : flg,
      });
    },

    /**
     * 登録
     *
     * 登録ボタンをクリックして、
     * データを保存されます。
     * で表示のブロックを表示されます。
     */
    onRegist() {
      var me   = this;

      me.$confirm('登録します。よろしいですか？', '確認', {
        confirmButtonText: '登録',
        cancelButtonText: 'キャンセル',
        type: 'warning'
      }).then(() => {
        var data = me.registData;
        var cnt  = data.length;

        var param = [];
        var total = 0;

        // 最新のデータを入手する
        for (var i=0; i<cnt; i++) {
          param[data[i].date] = data[i];
          total++;
        }

        // ローダを表示する
        this.globalLoader = true;

        if (total < 1) {
          // 登録の場合
          me.$message({
            message: '登録が完了しました。',
            type: 'success'
          });
          this.globalLoader = false;
          return;
        }

        axios.post('/holiday/regist', {param: Object.assign({}, param)}).then(function(res) {
          var data = res.data.data;

          // ロードを止まれる
          me.$data.globalLoader = false;

          // load to 表示 block
          me.onView('表示', 'all');

          // 登録の場合
          me.$message({
            message: '登録が完了しました。',
            type: 'success'
          });
        });
      }).catch(() => {});
    }
  }
}
</script>
<style scoped>
.hide {
  display: none;
}

.showCalendar {
  display: block;
}

.calendar-block {
  display: inline-block;
  margin: 0 50px;
  padding: 10px;
}
.calendarTbl {
  width: 300px;
  margin: 20px;
  float: left;
}

.calendarTbl tr th {
  text-align: center;
  padding: 5px;
  border: dashed 1.5px #7b7676;
}

.calendarTbl tbody tr td {
  width: 80px;
  border: dashed 1.5px #7b7676;
}

.calendarTbl tbody tr td a {
  display: block;
  padding: 5px;
  width: 100%;
}

.calendarTbl tbody tr td span,
.calendarTbl tbody tr td a,
.calendarTbl tbody tr .emptyRowCol {
  height: 70px;
}


.holidayweekday a.publicHoliday {
  background: #f3a6a6 url(/common/images/icons/icon_holiday_close1.png) center 29px no-repeat;
}

.holidayBusinessday {
  background: #f3a6a6;
}

.publicHoliday {
  background: #f3a6a6 url(/common/images/icons/icon_holiday_close1.png) center 29px no-repeat;
}

.hyojiPublicHoliday {
  background: #f3a6a6 url(/common/images/icons/icon_holiday_close1.png) center 29px no-repeat;
}

.holiday {
  background: #eeece1 url(/common/images/icons/icon_holiday_close1.png) center 29px no-repeat;
}

.normal {
  background: url(/common/images/icons/icon_holiday_open.png) center 29px no-repeat;
}

.holidayweekend .holidayBusinessday,
.holidayweekday .holidayBusinessday {
  background: #f3a6a6 url(/common/images/icons/icon_holiday_open.png) center 29px no-repeat;
}


.publicHolidayweekday .normal,
.publicHolidayweekend .normal {
  background: none;
}

.publicHolidayweekday a.holidayBusinessday,
.publicHolidayweekend a.holidayBusinessday {
  background: #f3a6a6;
}

.publicHolidayweekday a.holiday,
.publicHolidayweekend a.holiday {
  background: #eeece1;
}

.publicHolidayweekday a.publicHoliday,
.publicHolidayweekend a.publicHoliday {
  background: #f3a6a6;
}

.holidayweekday,
.normal,
.holidayBusinessday,
.publicHoliday,
.hyojiPublicHoliday,
.holiday {
  width: 100%;
  height: 100%;
}

.holidayweekend .holiday,
.publicHolidayweekend .holiday,
.allweekend .holiday {
    height: 68px;
}

.yr2018 .holidayweekend .holiday,
.yr2018 .publicHolidayweekend .holiday,
.yr2018 .allweekend .holiday,
.yr2019 .holidayweekend .holiday,
.yr2019 .publicHolidayweekend .holiday,
.yr2019 .allweekend .holiday {
    height: 70px;
}
</style>