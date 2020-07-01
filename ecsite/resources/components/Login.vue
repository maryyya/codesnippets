<template>
  <div class="l_main">
    <div class="l_wrap">
      <div class="l_contents wid500 u_Mcenter">
        <div class="l_block u_ALcenter">
          <div v-html="msgHtml"></div>
          <el-form :model="loginForm" :rules="loginRules" ref="loginForm">
            <el-form-item label="ユーザ名" prop="usernm">
              <el-input type="text" v-model="loginForm.usernm"></el-input>
            </el-form-item>
            <el-form-item label="パスワード" prop="password">
              <el-input type="password" v-model="loginForm.password"></el-input>
            </el-form-item>
            <button type="button" class="u_Mcenter c_btn c_btn_type01 is_blue c_modal_link" v-on:click="onSubmit('loginForm');">ログイン</button>
          </el-form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    data() {
      var validateUsrnm = (rule, value, callback) => {
        if (value === '') {
          callback(new Error('入力してください。'));
        }

        callback();
      };

      var validatePass = (rule, value, callback) => {
        if (value === '') {
          callback(new Error('入力してください。'));
        }

        callback();
      };

      return {
        loginForm: {
          usernm  : '',
          password: ''
        },
        msgHtml: '',
        loginRules: {
          usernm: [
            {required: true,validator: validateUsrnm, trigger: 'false'}
          ],
          password: [
            {required: true,validator: validatePass, trigger: 'false'}
          ]
        }
      }
    },
    methods: {
      onSubmit(formName) {
        this.$refs[formName].validate((valid) => {
          if (!valid) {
            return false;
          }

          axios.post('/xyz/auth', this.loginForm).then(function(res) {
            var data = res.data;
            window.location.href = '/center/index';
          });
        });


      }
    }
  }
</script>
