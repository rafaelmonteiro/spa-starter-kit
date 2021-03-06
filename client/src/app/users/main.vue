
<script>
  import { mapActions, mapState } from 'vuex'

  export default {
    /**
    * Components name to be displayed on
    * Vue.js Devtools
    */
    name: 'CcUsers',

    methods: {
      edit(index) {
        const { id } = this.users[index]
        this.$router.push({
          name: 'users.edit',
          params: { id },
          query: { page: this.currentPage } })
      },
      create() {
        this.$router.push({ name: 'users.new', query: { page: this.currentPage } })
      },
      hide() {
        this.$router.push({ name: 'users.index', query: { page: this.currentPage } })
      },
      /**
      * Brings actions from Vuex to the scope of
      * this component
      */
      ...mapActions(['usersSetData', 'setFetching']),

      fetch() {
        this.fetchPaginated()
        this.fetchFullList()
      },

      /**
      * Fetch a new set of users
      * based on the current page
      */
      fetchPaginated() {
        /**
        * Vuex action to set fetching property
        * to true, thus showing the spinner
        * that is part of navbar.vue
        */
        this.setFetching({ fetching: true })

        /**
        * Makes the HTTP request to the API
        * $http is a Vue.js plugins exposing
        * an Axios object.
        * See /src/plugins/http.js
        */
        this.$http.get(`users?page=${this.currentPage}`).then(({ data }) => {
          /**
          * Vuex action to set pagination object in
          * the Vuex Users module
          */
          this.usersSetData({
            list: data.data,
            pagination: data.meta.pagination,
          })

          /**
          * Vuex action to set fetching property
          * to false, thus hiding the spinner
          * that is part of navbar.vue
          */
          this.setFetching({ fetching: false })
        })
      },

      /**
      * Differente from fetch() which always
      * return a paginated set of users
      * this one returns the full set, which
      * is used by other components in the app.
      */
      fetchFullList() {
        this.setFetching({ fetching: true })
        this.$http.get('users/full-list').then(({ data }) => {
          /**
          * Vuex action to set full list array in
          * the Vuex Users module
          */
          this.usersSetData({
            full_list: data.data,
          })
          this.setFetching({ fetching: false })
        })
      },

      /**
      * Navigate to a specific page, provided in the
      * obj received by the method
      */
      navigate(page) {
        /**
        * Push the page number to the query string
        */
        this.$router.push({ name: 'users.index', query: { page } })
      },

      /**
      * Shows a confirmation dialog
      */
      askRemove(index) {
        const user = this.users[index]
        swal({
          title: 'Are you sure?',
          text: `User ${user.name} will be permanently removed.`,
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#DD6B55',
          confirmButtonText: 'Yes, do it!',
          closeOnConfirm: false,
        }, () => this.remove(user)) // callback executed when OK is pressed
      },

      /**
      * Makes the HTTP requesto to the API
      */
      remove(user) {
        this.$http.delete(`users/${user.id}`).then(() => {
          /**
          * On success fetch a new set of Users
          * based on current page number
          */
          this.fetchPaginated()

          /**
          * If we remove a user then
          * the full list must be refreshed
          */
          this.fetchFullList()

          /**
          * Shows a different dialog based on the result
          */
          swal('Done!', 'User removed.', 'success')

          /**
          * Redirects back to the main list,
          * in case the form is open
          */
          if (this.isFormVisible) {
            this.$router.push({ name: 'users.index', query: { page: this.currentPage } })
          }
        })
        .catch((error) => {
          /**
          * Just shows the result in a form or error.
          * The general error message is displayed by
          * the action dispatched by the interceptor.
          * See /src/plugins/http.js
          */
          swal('Falha!', error.response.data.messages[0], 'error')
        })
      },
    },

    watch: {
      // when page change, fetch new data
      currentPage: 'fetchPaginated',
    },

    /**
    * mapState will bring indicated Vuex
    * state properties to the scope of this component.
    */
    computed: {
      ...mapState({
        fetching: state => state.fetching,
        pagination: state => state.Users.pagination,
        list: state => state.Users.list,
      }),
      users() {
        return this.list
      },
      currentPage() {
        return parseInt(this.$route.query.page, 10) || 1
      },
      isFormVisible() {
        return this.$route.name === 'users.new' || this.$route.name === 'users.edit'
      },
    },
    /**
    * Right before navigate out to another route
    * clears all event handlers, thus avoiding
    * multiple handlers to be set and the annoying
    * behaviour of multiple AJAX calls being made.
    */
    beforeRouteLeave(to, from, next) {
      this.$bus.$off('user.created')
      this.$bus.$off('user.updated')
      jQuery('body').off('keyup')
      next()
    },
    mounted() {
      /**
      * User was created or updated, refresh the list
      */
      this.$bus.$on('user.created', this.fetch)
      this.$bus.$on('user.updated', this.fetch)
      /**
      * Fetch data immediately after component is mounted
      */
      this.fetchPaginated()
    },
  }
</script>

<template>
  <div>
    <div class="row">
      <div class="col-md-6">
        <h1>User Management</h1>
      </div>
      <div class="col-md-6 text-right">
        <div class="button-within-header">
          <el-button
            @click="create"
            v-show="!isFormVisible"
            type="default"
            icon="plus"
            size="mini"></el-button>
          <el-button
            @click="hide"
            v-show="isFormVisible"
            type="default"
            icon="minus"
            size="mini"></el-button>
        </div>
      </div>
    </div>

    <!-- Form to create/edit will be inserted here  -->
    <!-- when navigate to /nova or /editar/{id}  -->
    <!-- see /src/modules/users/routes.js  -->
    <transition name="fade">
      <router-view></router-view>
    </transition>

    <!-- el-table and its children comes from Element UI -->
    <!-- http://element.eleme.io/#/en-US/component/table -->
    <el-table v-loading.body="fetching" :data="users" stripe border style="width: 100%">
      <el-table-column prop="id" label="ID" width="80"></el-table-column>
      <el-table-column prop="name" label="User"></el-table-column>
      <el-table-column prop="email" label="Email"></el-table-column>
      <el-table-column inline-template label="Options" width="100">
        <div>
          <el-button @click="edit($index)" type="default" icon="edit" size="mini"></el-button>
          <el-button @click="askRemove($index)" type="default" icon="delete" size="mini"></el-button>
        </div>
      </el-table-column>
    </el-table>
    <div class="pagination">
      <el-pagination
        @current-change="navigate"
        :current-page="pagination.current_page"
        :page-size="pagination.per_page"
        layout="total, prev, pager, next, jumper"
        :total="pagination.total">
      </el-pagination>
    </div>
  </div>
</template>

<style scoped>
  .pagination {
    float: right;
    margin-top: 20px;
  }
  .button-within-header {
    margin-top: 32px;
  }
  .fade-enter-active, .fade-leave-active {
    transition: opacity .7s ease;
  }
  .fade-enter, .fade-leave-active {
    opacity: 0;
  }
</style>
