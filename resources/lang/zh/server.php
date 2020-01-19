<?php

return [
    'index' => [
        'title' => '查看服务器 :name ',
        'header' => '服务器控制台',
        'header_sub' => '实时掌控您的服务器。',
    ],
    'schedule' => [
        'header' => '计划任务',
        'header_sub' => '一处轻松掌管服务器任务。',
        'current' => '当前计划',
        'new' => [
            'header' => '新建计划',
            'header_sub' => '为此服务器新建一组计划任务。',
            'submit' => '创建任务',
        ],
        'manage' => [
            'header' => '管理任务',
            'submit' => '修改任务',
            'delete' => '删除任务',
        ],
        'task' => [
            'time' => '在···之后',
            'action' => '执行操作',
            'payload' => '任务内容',
            'add_more' => '添加其他任务',
        ],
        'actions' => [
            'command' => '发送命令',
            'power' => '电源命令',
        ],
        'toggle' => '更改状态',
        'run_now' => '触发任务',
        'schedule_created' => '已成功在此服务器上新建计划任务。',
        'schedule_updated' => '已更新计划任务。',
        'unnamed' => '未命名任务',
        'setup' => '任务配置',
        'day_of_week' => '星期',
        'day_of_month' => '日',
        'hour' => '时',
        'minute' => '分',
        'time_help' => '计划任务系统支持使用 Cronjob 语法来定义任务启动时间。使用上方的字段来指定计划任务的开始时间或选择多选菜单中的多个选项。',
        'task_help' => '任务时间与先前定义的任务紧密相关。每个计划最多可分配 5 项任务且任务时间间隔不得超过 15 分钟。',
    ],
    'tasks' => [
        'task_created' => '已成功在面板上新建任务。',
        'task_updated' => '已成功更新任务。现有的所有队列中的任务操作将被取消并于下个定义时间执行。',
        'header' => '计划任务',
        'header_sub' => '自动化您的服务器。',
        'current' => '当前计划任务',
        'actions' => [
            'command' => '发送命令',
            'power' => '发送电源指令',
        ],
        'new_task' => '新增任务',
        'toggle' => '更改状态',
        'new' => [
            'header' => '新建任务',
            'header_sub' => '为此服务器新建计划任务。',
            'task_name' => '任务名称',
            'day_of_week' => '星期',
            'custom' => '自定义',
            'day_of_month' => '日',
            'hour' => '时',
            'minute' => '分',
            'sun' => '星期日',
            'mon' => '星期一',
            'tues' => '星期二',
            'wed' => '星期三',
            'thurs' => '星期四',
            'fri' => '星期五',
            'sat' => '星期六',
            'submit' => '创建任务',
            'type' => '任务类型',
            'chain_then' => '先···再···',
            'chain_do' => '执行',
            'chain_arguments' => '使用参数',
            'payload' => '任务内容',
            'payload_help' => '例如，若您选择<code>发送命令</code>，请在此处填写要发送的命令。若您选择<code>发送电源指令</code>，请在此处填写电源命令（如<code>重启</code>）.',
        ],
        'edit' => [
            'header' => '任务管理',
            'submit' => '更新任务',
        ],
    ],
    'users' => [
        'header' => '用户管理',
        'header_sub' => '掌控谁能访问您的服务器。',
        'configure' => '配置权限',
        'list' => '权限用户',
        'add' => '新增子用户',
        'update' => '更新子用户',
        'user_assigned' => '已成功分配新子用户至此服务器。',
        'user_updated' => '已成功更新权限。',
        'edit' => [
            'header' => '编辑子用户',
            'header_sub' => '编辑此用户的服务器访问权限。',
        ],
        'new' => [
            'header' => '新增新用户',
            'header_sub' => '新增允许访问此服务器的用户。',
            'email' => '电子邮件地址',
            'email_help' => '输入您邀请管理此服务器用户的电子邮件地址。',
            'power_header' => '电源管理',
            'file_header' => '文件管理',
            'subuser_header' => '子用户管理',
            'server_header' => '服务器管理',
            'task_header' => '计划任务管理',
            'database_header' => '数据库管理',
            'power_start' => [
                'title' => '启动服务器',
                'description' => '允许此用户启动服务器。',
            ],
            'power_stop' => [
                'title' => '停止服务器',
                'description' => '允许此用户停止服务器。',
            ],
            'power_restart' => [
                'title' => '重新启动服务器',
                'description' => '允许此用户重新启动服务器。',
            ],
            'power_kill' => [
                'title' => '强制关闭服务器',
                'description' => '允许此用户强行关闭服务器。',
            ],
            'send_command' => [
                'title' => '发送控制台命令',
                'description' => '允许用户发送控制台命令。若用户没有“停止服务器”权限，则其 stop 命令。',
            ],
            'access_sftp' => [
                'title' => 'SFTP 权限',
                'description' => '允许用户连接到守护程序所提供的 SFTP 服务器。',
            ],
            'list_files' => [
                'title' => '列出文件',
                'description' => '允许用户列出服务器上所有文件及文件夹，但是无法查看文件内容。',
            ],
            'edit_files' => [
                'title' => '编辑文件',
                'description' => '允许用户打开文件查看内容。SFTP 不受此权限影响。',
            ],
            'save_files' => [
                'title' => '保存文件',
                'description' => '允许用户保存编辑过的文件内容。SFTP 不受此权限影响。',
            ],
            'move_files' => [
                'title' => '重命名与移动文件',
                'description' => '允许用户在文件系统上重命名与移动文件及文件夹。',
            ],
            'copy_files' => [
                'title' => '复制文件',
                'description' => '允许用户在文件系统上复制文件及文件夹。',
            ],
            'compress_files' => [
                'title' => '压缩文件',
                'description' => '允许用户在文件系统上压缩文件及文件夹。',
            ],
            'decompress_files' => [
                'title' => '解压文件',
                'description' => '允许用户解压 .zip 和 .tar（.gz）归档文件。',
            ],
            'create_files' => [
                'title' => '创建文件',
                'description' => '允许用户通过面板创建文件。',
            ],
            'upload_files' => [
                'title' => '上传文件',
                'description' => '允许用户通过文件管理上传文件。',
            ],
            'delete_files' => [
                'title' => '删除文件',
                'description' => '允许用户删除文件系统上的文件。',
            ],
            'download_files' => [
                'title' => '下载文件',
                'description' => '允许用户下载文件。若用户被给予此权限，其可以在下载后查看文件而无需所需面板权限。',
            ],
            'list_subusers' => [
                'title' => '列出子用户',
                'description' => '允许用户访问此服务器的子用户列表。',
            ],
            'view_subuser' => [
                'title' => '查看子用户',
                'description' => '允许用户查看子用户的权限。',
            ],
            'edit_subuser' => [
                'title' => '编辑子用户',
                'description' => '允许用户编辑此服务器上的子用户权限。',
            ],
            'create_subuser' => [
                'title' => '创建子用户',
                'description' => '允许用户在此服务器上添加子用户。',
            ],
            'delete_subuser' => [
                'title' => '删除子用户',
                'description' => '允许用户删除此服务器上的子用户。',
            ],
            'view_allocations' => [
                'title' => '查看分配',
                'description' => '允许用户查看所有分配到此服务器上的 IP 及端口。',
            ],
            'edit_allocation' => [
                'title' => '编辑默认连接',
                'description' => '允许用户更改此服务器的默认连接地址。',
            ],
            'view_startup' => [
                'title' => '查看启动参数',
                'description' => '允许用户访问服务器的启动参数和相关变量。',
            ],
            'edit_startup' => [
                'title' => '编辑启动参数',
                'description' => '允许用户更改服务器的启动参数。',
            ],
            'list_schedules' => [
                'title' => '列出计划任务',
                'description' => '允许用户列出服务器上的所有计划任务（无论是否启用）。',
            ],
            'view_schedule' => [
                'title' => '查看计划',
                'description' => '允许用户查看计划任务的详细信息，包含执行时间及分配任务。',
            ],
            'toggle_schedule' => [
                'title' => '开关计划',
                'description' => '允许用户启用或禁用计划的。',
            ],
            'queue_schedule' => [
                'title' => '队列计划',
                'description' => '允许用户将计划纳入队列在下个周期执行。',
            ],
            'edit_schedule' => [
                'title' => '编辑计划',
                'description' => '允许用户编辑计划，包括所有的执行任务。这将允许用户移除单个任务，但无法移除计划本身。',
            ],
            'create_schedule' => [
                'title' => '创建计划',
                'description' => '允许用户新建计划任务。',
            ],
            'delete_schedule' => [
                'title' => '删除计划',
                'description' => '允许用户从服务器删除计划。',
            ],
            'view_databases' => [
                'title' => '查看数据库信息',
                'description' => '允许用户查看所有与此服务器相关联的数据库及其用户名与密码信息。',
            ],
            'reset_db_password' => [
                'title' => '重置数据库',
                'description' => '允许用户重置服务器数据库密码。',
            ],
            'delete_database' => [
                'title' => '删除数据库',
                'description' => '允许用户从面板删除此服务器数据库。',
            ],
            'create_database' => [
                'title' => '新建数据库',
                'description' => '允许用户为此服务器新建数据库。',
            ],
        ],
    ],
    'allocations' => [
        'mass_actions' => '批量操作',
        'delete' => '删除分配地址',
    ],
    'files' => [
        'exceptions' => [
            'invalid_mime' => '此类型文件无法通过面板内置编辑器编辑。',
            'max_size' => '此文件过大，无法使用面板内置编辑器编辑。',
        ],
        'header' => '文件管理',
        'header_sub' => '从网页直接管理您的所有文件。',
        'loading' => '正在加载初始文件结构，这可能需要几秒钟。',
        'path' => '当您配置插件或服务器设置的文件路径时，您应使用 :path 作为您的根目录。此节点通过网页上传的最大文件限制为 :size。',
        'seconds_ago' => '数秒前',
        'file_name' => '文件名',
        'size' => '大小',
        'last_modified' => '最后修改',
        'add_new' => '新建文件',
        'add_folder' => '新建文件夹',
        'mass_actions' => '批量操作',
        'delete' => '删除文件',
        'edit' => [
            'header' => '编辑文件',
            'header_sub' => '从网页编辑文件。',
            'save' => '保存文件',
            'return' => '返回文件管理',
        ],
        'add' => [
            'header' => '新建文件',
            'header_sub' => '在您服务器上新建新文件。',
            'name' => '文件名',
            'create' => '创建文件',
        ],
    ],
    'config' => [
        'name' => [
            'header' => '服务器名',
            'header_sub' => '更改服务器名称。',
            'details' => '此服务器名只是为了让您更好的管理服务器，并不会对向游戏内玩家显示的服务器配置造成影响。',
        ],
        'startup' => [
            'header' => '启动配置',
            'header_sub' => '控制服务器的启动参数。',
            'command' => '启动命令',
            'edit_params' => '编辑参数',
            'update' => '更新启动参数',
            'startup_regex' => '输入规则',
            'edited' => '已成功编辑启动变量。这将在下次服务器启动时发挥功用。',
        ],
        'sftp' => [
            'header' => 'SFTP 配置',
            'header_sub' => 'SFTP 连接所需的账户信息。',
            'details' => 'SFTP 信息',
            'conn_addr' => '连接地址',
            'warning' => 'SFTP 密码为您的账户密码。请确保您的客户端被设置为使用 SFTP 而非 FTP 或 FTPS，这些协议间存在差异。',
        ],
        'database' => [
            'header' => '数据库',
            'header_sub' => '此服务器的所有可用数据库。',
            'your_dbs' => '已配置的数据库',
            'host' => 'MySQL 主机',
            'reset_password' => '重置密码',
            'no_dbs' => '此服务器没有可用的数据库。',
            'add_db' => '新建新数据库。',
        ],
        'allocation' => [
            'header' => '服务器地址分配',
            'header_sub' => '控制此服务器可使用的 IP 地址和端口。',
            'available' => '可用分配地址',
            'help' => '分配地址帮助',
            'help_text' => '左方列表列出了您可用于传入连接的所有可用 IP 地址及端口。',
        ],
    ],
];
