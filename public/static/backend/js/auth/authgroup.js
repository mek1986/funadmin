define(['jquery', 'table', 'form'], function ($, Table, Form) {

    let Controller = {
        index: function () {
            Table.init = {
                table_elem: 'list',
                tablId: 'list',
                requests: {
                    modify_url: 'auth.authgroup/modify',
                    index_url: 'auth.authgroup/index',
                    del_url: 'auth.authgroup/delete',
                    add_url: 'auth.authgroup/add',
                    edit_url: 'auth.authgroup/edit',
                    access:  {
                        type: 'open',
                        class: 'layui-btn-nomarl',
                        icon: 'layui-icon-add-circle-fine',
                        url: 'auth.authgroup/access',
                        text: __('Access Group'),
                        title:  __('Access Group'),
                    },

                },
            },
            Table.render({
                    elem: '#' + Table.init.table_elem,
                    id: Table.init.tablId,
                    url: Speed.url(Table.init.requests.index_url),
                    init: Table.init,
                    toolbar: ['refresh', 'add', 'delete'],
                    cols: [[
                        {checkbox: true, fixed: true},
                        {field: 'id', title: 'ID', width: 80, fixed: true, sort: true},
                        {field: 'pid', title: '', width: 150,},
                        {field: 'title', title: __('GroupName'), width: 120,},
                        {
                            field: 'status',
                            title: __('Status'),
                            width: 120,
                            search: 'select',
                            selectList: {0: __('Disabled'), 1: __('Enabled')},
                            filter: 'status',
                            templet: Table.templet.switch
                        },
                        {field: 'create_time', title: __('CreateTime'), width: 180,templet:Table.templet.time},
                        {field: 'update_time', title: __('UpdateTime'), width: 180, templet: Table.templet.time},
                        {
                            width: 250,
                            align: 'center',
                            title: __('Operat'),
                            init: Table.init,
                            templet: Table.templet.operat,
                            operat: ['edit', 'delete']
                        }

                    ]],
                    limits: [10, 15, 20, 25, 50, 100],
                    limit: 15,
                    page: true
                });
            let table = $('#' + Table.init.table_elem);
            Table.api.bindEvent(table);
        },
        add: function () {

            Controller.api.bindevent()
        },
        edit: function () {
            Controller.api.bindevent()

        },
        api: {
            bindevent: function () {
                Form.api.bindEvent($('form'))
            }
        }

    };
    return Controller;
});