create table clients
(
    id         bigint unsigned auto_increment
        primary key,
    name       varchar(150)                       not null,
    city       varchar(100)                       null,
    state      char(2)                            null,
    created_at datetime default CURRENT_TIMESTAMP not null,
    updated_at datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at datetime                           null
);

create index idx_clients_name
    on clients (name);

create table orders
(
    id                bigint unsigned auto_increment
        primary key,
    order_number      varchar(20)                                                                                                not null,
    tracking_code     varchar(20)                                                                                                null,
    client_id         bigint unsigned                                                                                            not null,
    product_qty       int unsigned                                                                                               null,
    item_qty          int unsigned                                                                                               null,
    invoice_number    varchar(20)                                                                                                null,
    order_date        date                                                                                                       null,
    freight_type      enum ('own_fleet', 'cif_carrier', 'fob_client')                                                            null,
    vehicle_type      varchar(50)                                                                                                null,
    driver_name       varchar(150)                                                                                               null,
    freight_value     decimal(12, 2)                                                                                             null,
    loading_date      date                                                                                                       null,
    delivery_date     date                                                                                                       null,
    expected_delivery date                                                                                                       null,
    status            enum ('in_production', 'awaiting_loading', 'in_transit', 'delivered', 'pending') default 'pending'         not null,
    is_pending        tinyint(1)                                                                       default 0                 not null,
    source            enum ('spreadsheet', 'system')                                                   default 'system'          not null,
    created_at        datetime                                                                         default CURRENT_TIMESTAMP not null,
    updated_at        datetime                                                                         default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at        datetime                                                                                                   null,
    constraint tracking_code
        unique (tracking_code),
    constraint uq_orders_number
        unique (order_number),
    constraint fk_orders_client
        foreign key (client_id) references clients (id)
            on delete cascade
);

create index idx_orders_client
    on orders (client_id);

create index idx_orders_date
    on orders (order_date);

create index idx_orders_status
    on orders (status);

create table users
(
    id                         bigint unsigned auto_increment
        primary key,
    name                       varchar(150)                                                                     not null,
    email                      varchar(150)                                                                     not null,
    password                   varchar(255)                                                                     not null,
    role                       enum ('admin', 'manager', 'dispatcher', 'stakeholder') default 'dispatcher'      not null,
    avatar                     varchar(255)                                                                     null,
    is_active                  tinyint(1)                                             default 1                 not null,
    email_verified_at          datetime                                                                         null,
    email_verification_token   varchar(64)                                                                      null,
    email_verification_sent_at datetime                                                                         null,
    reset_token                varchar(64)                                                                      null,
    reset_expires_at           datetime                                                                         null,
    created_at                 datetime                                               default CURRENT_TIMESTAMP not null,
    updated_at                 datetime                                               default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at                 datetime                                                                         null,
    constraint uq_users_email
        unique (email)
);

create table audit_logs
(
    id          bigint unsigned auto_increment
        primary key,
    user_id     bigint unsigned                    null,
    event       varchar(100)                       not null,
    description varchar(255)                       null,
    ip_address  varchar(45)                        null,
    user_agent  varchar(255)                       null,
    metadata    json                               null,
    created_at  datetime default CURRENT_TIMESTAMP not null,
    constraint fk_audit_user
        foreign key (user_id) references users (id)
            on delete set null
);

create index idx_audit_user_event
    on audit_logs (user_id, event, created_at);

create table imports
(
    id            bigint unsigned auto_increment
        primary key,
    user_id       bigint unsigned                        not null,
    file_name     varchar(255)                           not null,
    total_rows    int unsigned default '0'               not null,
    created_count int unsigned default '0'               not null,
    updated_count int unsigned default '0'               not null,
    error_count   int unsigned default '0'               not null,
    created_at    datetime     default CURRENT_TIMESTAMP not null,
    constraint fk_imports_user
        foreign key (user_id) references users (id)
);

create table order_status_history
(
    id          bigint unsigned auto_increment
        primary key,
    order_id    bigint unsigned                    not null,
    user_id     bigint unsigned                    null,
    from_status varchar(30)                        null,
    to_status   varchar(30)                        not null,
    created_at  datetime default CURRENT_TIMESTAMP not null,
    constraint fk_status_history_order
        foreign key (order_id) references orders (id)
            on delete cascade,
    constraint fk_status_history_user
        foreign key (user_id) references users (id)
            on delete set null
);

create index idx_status_history_order
    on order_status_history (order_id, created_at);

create table sessions
(
    id            varchar(128)                       not null
        primary key,
    user_id       bigint unsigned                    null,
    ip_address    varchar(45)                        null,
    user_agent    varchar(255)                       null,
    payload       longtext                           not null,
    last_activity int unsigned                       not null,
    created_at    datetime default CURRENT_TIMESTAMP not null,
    constraint fk_sessions_user
        foreign key (user_id) references users (id)
            on delete cascade
);

create index idx_sessions_last_activity
    on sessions (last_activity);

create index idx_sessions_user
    on sessions (user_id);

