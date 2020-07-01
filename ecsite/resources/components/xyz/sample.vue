<template>
  <div class="l_main">
    <div class="l_wrap">
      <div class="l_contents">
        <div class="l_block">
          <table class="c_table_type01">
            <tr>
              <th>Input</th>
              <td><el-input placeholder="Please input" v-model="input"></el-input></td>
              <th>Datepicker</th>
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
              <th>Checkbox</th>
              <td>
                <el-checkbox-group v-model="checkList">
                  <el-checkbox label="Option A"></el-checkbox>
                  <el-checkbox label="Option B"></el-checkbox>
                  <el-checkbox label="disabled" disabled></el-checkbox>
                </el-checkbox-group>
              </td>
              <th>Radio</th>
              <td>
                <el-radio v-model="radio" label="1">Option A</el-radio>
                <el-radio v-model="radio" label="2">Option B</el-radio>
              </td>
            </tr>
            <tr>
              <th>Select</th>
              <td>
                <el-select v-model="select" placeholder="Select">
                  <el-option
                    v-for="item in options"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
                  </el-option>
                </el-select>
              </td>
              <th>Select(cascader)</th>
              <td>
                <div class="block">
                  <el-cascader
                    :options="options"
                    v-model="selectedOptions"
                    @change="handleChange">
                  </el-cascader>
                </div>
              </td>
            </tr>
            <tr>
              <th>Tooltip</th>
              <td>
                <el-tooltip content="Tooltip Content" placement="top">
                  <el-button>Tooltip Dark</el-button>
                </el-tooltip>
                <el-tooltip content="Tooltip Content" placement="bottom" effect="light">
                  <el-button>Tooltip Light</el-button>
                </el-tooltip>
              </td>
              <th>Upload</th>
              <td>
                <el-upload
                  class="upload-demo"
                  action="https://jsonplaceholder.typicode.com/posts/"
                  :on-preview="handlePreview"
                  :on-remove="handleRemove"
                  :before-remove="beforeRemove"
                  multiple
                  :limit="3"
                  :on-exceed="handleExceed"
                  :file-list="fileList">
                  <el-button size="small" type="primary">Upload</el-button>
                  <div slot="tip" class="el-upload__tip">jpg/png files with a size less than 500kb</div>
                </el-upload>
              </td>
            </tr>
            <tr>
              <th>MessageBox</th>
              <td>
                <el-button type="text" @click="open2">Click to open the Message Box</el-button>
              </td>
              <th>Notification</th>
              <td>
                <el-button
                  plain
                  @click="open3">
                  Success
                </el-button>
                <el-button
                  plain
                  @click="open4">
                  Warning
                </el-button><br><br>
                <el-button
                  plain
                  @click="open5">
                  Info
                </el-button>
                <el-button
                  plain
                  @click="open6">
                  Error
                </el-button>
              </td>
            </tr>
            <tr>
              <th>Dialog</th>
              <td colspan="3">
                <el-button type="text" @click="dialogTableVisible = true">Open Dialog</el-button>
                <el-dialog title="Shipping address" :visible.sync="dialogTableVisible">
                  <el-table :data="gridData">
                    <el-table-column property="date" label="Date" width="150"></el-table-column>
                    <el-table-column property="name" label="Name" width="200"></el-table-column>
                    <el-table-column property="address" label="Address"></el-table-column>
                  </el-table>
                </el-dialog>
              </td>
            </tr>
            <tr>
              <th>Loading</th>
              <td colspan="3">
                <el-table
                  v-loading="loading"
                  :data="tableData"
                  style="width: 100%">
                  <el-table-column
                    prop="date"
                    label="Date"
                    width="180">
                  </el-table-column>
                  <el-table-column
                    prop="name"
                    label="Name"
                    width="180">
                  </el-table-column>
                  <el-table-column
                    prop="address"
                    label="Address">
                  </el-table-column>
                </el-table>
              </td>
            </tr>
            <tr>
              <th>Datatable</th>
              <td colspan="3">
                <div style="margin-bottom: 10px">
                  <el-row>
                    <el-col :span="18">
                      <el-button @click="onCreate">create 1 row</el-button>
                      <el-button @click="onCreate100">create 100 row</el-button>
                      <el-button @click="bulkDelete">bulk delete</el-button>
                    </el-col>
                  </el-row>
                </div>
                <data-tables :data="datatablesss" :action-col="actionCol" :filters="filters" @selection-change="handleSelectionChange">
                  <el-table-column type="selection" width="55">
                  </el-table-column>

                  <el-table-column v-for="title in titleaaa" :prop="title.prop" :label="title.label" :key="title.prop" sortable="custom">
                  </el-table-column>
                </data-tables>
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    data() {
      var datatablesss = [{
        "content": "Water flood",
        "flow_no": "FW201601010001",
        "flow_type": "Repair",
        "flow_type_code": "repair",
        }, {
        "content": "Lock broken",
        "flow_no": "FW201601010002",
        "flow_type": "Repair",
        "flow_type_code": "repair",
        }, {
        "content": "Help to buy some drinks",
        "flow_no": "FW201601010003",
        "flow_type": "Help",
        "flow_type_code": "help"
      }];

      var titleaaa = [{
        prop: "flow_no",
        label: "NO."
        }, {
        prop: "content",
        label: "Content"
        }, {
        prop: "flow_type",
        label: "Type"
      }];

      return {
        input      : '',
        orderdtFrom: '',
        orderdtTo  : '',
        checkList  : [],
        radio      : '1',
        options    : [{
          value      : 'A',
          label      : 'A',
          children   : [{
            value: 'Aa',
            label: 'Aa',
            children: [{
              value: 'Aaa',
              label: 'Aaa'
            }, {
              value: 'Aab',
              label: 'Aab'
            }, {
              value: 'Aac',
              label: 'Aac'
            }, {
              value: 'Aad',
              label: 'Aad'
            }]
          }, {
            value: 'Ab',
            label: 'Ab',
            children: [{
              value: 'Aba',
              label: 'Aba'
            }, {
              value: 'Abb',
              label: 'Abb'
            }]
          }]
        }, {
          value: 'B',
          label: 'B',
          children: [{
            value: 'Ba',
            label: 'Ba'
          }, {
            value: 'Bb',
            label: 'Bb'
          }, {
            value: 'Bc',
            label: 'Bc'
          }]
        }],
        select: '',
        selectedOptions: [],
        fileList: [{name: 'food.jpeg', url: 'https://fuss10.elemecdn.com/3/63/4e7f3a15429bfda99bce42a18cdd1jpeg.jpeg?imageMogr2/thumbnail/360x360/format/webp/quality/100'}, {name: 'food2.jpeg', url: 'https://fuss10.elemecdn.com/3/63/4e7f3a15429bfda99bce42a18cdd1jpeg.jpeg?imageMogr2/thumbnail/360x360/format/webp/quality/100'}],
        tableData: [{
          date: '2016-05-02',
          name: 'John Smith',
          address: 'No.1518,  Jinshajiang Road, Putuo District'
        }, {
          date: '2016-05-04',
          name: 'John Smith',
          address: 'No.1518,  Jinshajiang Road, Putuo District'
        }, {
          date: '2016-05-01',
          name: 'John Smith',
          address: 'No.1518,  Jinshajiang Road, Putuo District'
        }],
        loading: true,
        gridData: [{
          date: '2016-05-02',
          name: 'John Smith',
          address: 'No.1518,  Jinshajiang Road, Putuo District'
        }, {
          date: '2016-05-04',
          name: 'John Smith',
          address: 'No.1518,  Jinshajiang Road, Putuo District'
        }, {
          date: '2016-05-01',
          name: 'John Smith',
          address: 'No.1518,  Jinshajiang Road, Putuo District'
        }, {
          date: '2016-05-03',
          name: 'John Smith',
          address: 'No.1518,  Jinshajiang Road, Putuo District'
        }],
        dialogTableVisible: false,
        dialogFormVisible: false,
        form: {
          name: '',
          region: '',
          date1: '',
          date2: '',
          delivery: false,
          type: [],
          resource: '',
          desc: ''
        },
        formLabelWidth: '120px',
        datatablesss,
        titleaaa,
        filters: [{
          prop: 'flow_no',
          value: ''
        }],
        actionCol: {
          props: {
            label: 'Actionssss',
          },
          buttons: [{
            props: {
              type: 'primary'
            },
            handler: row => {
              this.$message('Edit clicked')
              row.flow_no = 'hello word' + Math.random()
              row.content = Math.random() > 0.5 ? 'Water flood' : 'Lock broken'
              row.flow_type = Math.random() > 0.5 ? 'Repair' : 'Help'
            },
            label: 'Edit'
          }, {
            handler: row => {
              this.datatablesss.splice(this.datatablesss.indexOf(row), 1)
              this.$message('delete success')
            },
            label: 'delete'
          }]
        },
        selectedRow: []
      }
    },
    methods: {
      handleChange(value) {
        console.log(value);
      },
      handleRemove(file, fileList) {
        console.log(file, fileList);
      },
      handlePreview(file) {
        console.log(file);
      },
      handleExceed(files, fileList) {
        this.$message.warning(`The limit is 3, you selected ${files.length} files this time, add up to ${files.length + fileList.length} totally`);
      },
      beforeRemove(file, fileList) {
        return this.$confirm(`Confirm remove file: ${ file.name }？`);
      },
      open2() {
        this.$confirm('This will permanently delete the file. Continue?', 'Warning', {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning'
        }).then(() => {
          this.$message({
            type: 'success',
            message: 'Delete completed'
          });
        }).catch(() => {
          this.$message({
            type: 'info',
            message: 'Delete canceled'
          });
        });
      },
      open3() {
        this.$notify({
          title: 'Success',
          message: 'This is a success message',
          type: 'success'
        });
      },
      open4() {
        this.$notify({
          title: 'Warning',
          message: 'This is a warning message',
          type: 'warning'
        });
      },

      open5() {
        this.$notify.info({
          title: 'Info',
          message: 'This is an info message'
        });
      },

      open6() {
        this.$notify.error({
          title: 'Error',
          message: 'This is an error message'
        });
      },
      onCreate() {
        this.datatablesss.push({
          content: "new created",
          flow_no: "FW201601010003" + Math.floor(Math.random() * 100),
          flow_type: "Help",
          flow_type_code: "help"
        })
      },
      onCreate100() {
        [...new Array(100)].map(_ => {
          this.onCreate()
        })
      },
      handleSelectionChange(val) {
        this.selectedRow = val
      },
      bulkDelete() {
        this.selectedRow.map(row => {
          this.datatablesss.splice(this.datatablesss.indexOf(row), 1)
        })
        this.$message('bulk delete success')
      }
    }
  }
</script>