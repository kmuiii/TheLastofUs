create database tlou_db;

use tlou_db;

create table users (
    user_id int primary key auto_increment,
    email varchar(100) not null unique,
    username varchar(100) not null,
    role varchar(50) default 'user',
    password varchar(100) not null,
    reset_code varchar(10) default null,
    reset_expired datetime default null,
    created_at datetime default current_timestamp,
    updated_at datetime default current_timestamp on update current_timestamp
);

insert into users (email, username, role, password) values
('admin@gmail.com', 'admin', 'admin', 'admin123'),
('user@gmail.com', 'user', 'user', 'user123');

