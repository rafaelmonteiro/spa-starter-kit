
<script>
  import { mapActions } from 'vuex'

  export default {
    name: 'CcUsersForm',

    /**
    * Component's local state
    */
    data() {
      return {
        user: {
          id: 0,
          name: '',
          email: '',
          password: '',
        },
      }
    },

    /**
    * Fetch user when component is first mounted
    */
    mounted() {
      this.fetch()
    },

    /**
    * Also fetch user every time route changes
    */
    watch: {
      $route: 'fetch',
    },

    /**
    * Determines based on the presence of
    * user id if the current actions
    * is editing instead of creating.
    */
    computed: {
      isEditing() {
        return this.user.id > 0
      },
      isValid() {
        this.resetMessages()
        if (this.user.name === '') {
          this.setMessage({ type: 'error', message: ['Please fill user name'] })
          return false
        }
        if (this.user.email === '') {
          this.setMessage({ type: 'error', message: ['Please fill user email'] })
          return false
        }
        return true
      },
    },

    methods: {
      ...mapActions(['setFetching', 'resetMessages', 'setMessage']),

      /**
      * If there's an ID in the route params
      * then use it to fetch the user
      * from the server
      */
      fetch() {
        const id = this.$route.params.id

        /**
        * This same component is used for create
        * and update so we have to check if
        * ID is not undefined.
        */
        if (id !== undefined) {
          /**
          * Fetch the user from the server
          */
          this.setFetching({ fetching: true })
          this.$http.get(`users/${id}`).then((res) => {
            const { id: _id, name, email, password } = res.data.data // http://wesbos.com/destructuring-renaming/
            this.user.id = _id
            this.user.name = name
            this.user.email = email
            this.user.password = password
            this.setFetching({ fetching: false })
          })
        }
      },
      submit() {
        /**
        * Pre-conditions are met
        */
        if (this.isValid) {
          /**
          * Shows the global spinner
          */
          this.setFetching({ fetching: true })

          if (this.isEditing) {
            this.update()
          } else {
            this.save()
          }
        }
      },
      save() {
        this.$http.post('users', { name: this.user.name, email: this.user.email, password: this.user.password }).then(() => {
          /**
          * This event will notify the world about
          * the user creation. In this case
          * the User main component will intercept
          * the event and refresh the list.
          */
          this.$bus.$emit('user.created')

          /**
          * Hides the global spinner
          */
          this.setFetching({ fetching: false })

          /**
          * Sets the global feedback message
          */
          this.setMessage({ type: 'success', message: 'New user was created' })

          /**
          * Resets component's state
          */
          this.reset()
        })
      },
      update() {
        this.$http.put(`users/${this.user.id}`, this.user).then(() => {
          /**
          * This event will notify the world about
          * the user creation. In this case
          * the User main component will intercept
          * the event and refresh the list.
          */
          this.$bus.$emit('user.updated')

          /**
          * Hides the global spinner
          */
          this.setFetching({ fetching: false })

          /**
          * Sets the global feedback message
          */
          this.setMessage({ type: 'success', message: 'User was updated' })
        })
      },
      reset() {
        this.user.id = 0
        this.user.name = ''
        this.user.email = ''
        this.user.password = ''
      },
    },
  }
</script>

<template>
  <div class="well">
    <el-form ref="form" :model="user" label-width="120px">
      <el-form-item label="User name">
        <el-input v-model="user.name"></el-input>
      </el-form-item>
      <el-form-item label="User email">
        <el-input v-model="user.email"></el-input>
      </el-form-item>
      <el-form-item label="User password">
        <el-input v-model="user.password" type="password"></el-input>
      </el-form-item>
      <el-button @click="submit">Save</el-button>
    </el-form>
  </div>
</template>
