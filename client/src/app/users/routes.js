
/**
* Components are lazy-loaded - See "Grouping Components in the Same Chunk"
* http://router.vuejs.org/en/advanced/lazy-loading.html
*/
/* eslint-disable global-require */
const Users = r => require.ensure([], () => r(require('./main')), 'users-bundle')
const Form = r => require.ensure([], () => r(require('./form')), 'users-bundle')

const meta = {
  requiresAuth: true,
}

export default [
  {
    name: 'users.index',
    path: '/users',
    component: Users,
    meta,
    children: [
      {
        name: 'users.new',
        path: 'create',
        component: Form,
        meta,
      }, {
        name: 'users.edit',
        path: ':id/edit',
        component: Form,
        meta,
      },
    ],
  },
]
