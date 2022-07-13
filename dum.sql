create table channel
(
    id          int auto_increment
        primary key,
    name        varchar(128) not null,
    description text         not null,
    data        int          null,
    trusted     bit          null
)
    charset = utf8mb4
    auto_increment = 4;

create table field
(
    id          int          not null,
    name        varchar(128) not null,
    description text         not null,
    data        int          not null
)
    charset = utf8mb4;

create table hashtags
(
    id   int auto_increment
        primary key,
    data text not null
)
    charset = utf8mb4;

create table messages
(
    id           int auto_increment
        primary key,
    `#_id`       varchar(128) not null,
    message_data text         not null,
    save         int          null
)
    charset = utf8mb4;

create table users
(
    id       int auto_increment
        primary key,
    name     varchar(256) not null,
    email    varchar(128) not null,
    password varchar(128) not null
)
    charset = utf8mb4;

