== Table structure for table admins

|------
|Column|Type|Null|Default
|------
|//**id**//|int(11)|No|
|**username**|varchar(50)|No|
|**email**|varchar(100)|No|
|password_hash|varchar(255)|No|
|full_name|varchar(100)|Yes|NULL
|role|enum('superadmin', 'admin', 'moderator')|Yes|admin
|is_active|tinyint(1)|Yes|1
|last_login|datetime|Yes|NULL
|created_at|datetime|Yes|current_timestamp()
|updated_at|datetime|Yes|current_timestamp()
== Table structure for table deposits

|------
|Column|Type|Null|Default
|------
|//**id**//|int(11)|No|
|user_id|int(11)|Yes|NULL
|method_id|int(11)|Yes|NULL
|proof|varchar(255)|Yes|NULL
|status|int(11)|Yes|0
|created_at|timestamp|Yes|current_timestamp()
|updated_at|timestamp|Yes|NULL
|amount|decimal(10,2)|Yes|0.00
|paid_amount|decimal(18,2)|Yes|NULL
|paid_currency|varchar(10)|Yes|NULL
== Table structure for table news

|------
|Column|Type|Null|Default
|------
|//**id**//|int(11)|No|
|title|text|Yes|NULL
|status|tinyint(1)|Yes|1
|created_at|timestamp|Yes|current_timestamp()
|updated_at|timestamp|Yes|NULL
== Table structure for table payment_methods

|------
|Column|Type|Null|Default
|------
|//**id**//|int(11)|No|
|name|varchar(50)|Yes|NULL
|image|varchar(255)|Yes|NULL
|status|int(11)|Yes|1
|wallet_address|varchar(255)|Yes|NULL
|withdrawal_fee|decimal(10,2)|No|0.00
|qr_image|varchar(255)|Yes|NULL
|crypto|tinyint(1)|No|0
|type|enum('bank', 'momo')|No|bank
|network|varchar(100)|Yes|NULL
|account_name|varchar(150)|Yes|NULL
|account_number|varchar(150)|Yes|NULL
|currency|varchar(10)|Yes|USD
|conversion_rate|decimal(18,8)|Yes|1.00000000
|active_country|varchar(150)|Yes|NULL
|min_withdraw|decimal(18,8)|Yes|0.00000000
== Table structure for table task_reset

|------
|Column|Type|Null|Default
|------
|//**id**//|int(11)|No|
|reset_time|datetime|No|
== Table structure for table users

|------
|Column|Type|Null|Default
|------
|//**id**//|int(11)|No|
|**email**|varchar(120)|Yes|NULL
|**phone**|varchar(30)|Yes|NULL
|country|varchar(100)|Yes|NULL
|password|varchar(255)|No|
|invite_code|varchar(100)|Yes|NULL
|vip_level|int(11)|Yes|0
|balance|decimal(12,2)|Yes|0.00
|created_at|timestamp|Yes|current_timestamp()
|**referral_code**|varchar(20)|Yes|NULL
|referred_by|varchar(20)|Yes|NULL
|withdrawal_balance|decimal(12,2)|Yes|0.00
== Table structure for table user_vip

|------
|Column|Type|Null|Default
|------
|//**id**//|int(11)|No|
|user_id|int(11)|Yes|NULL
|vip_id|int(11)|Yes|NULL
|start_time|datetime|Yes|NULL
|end_time|datetime|Yes|NULL
|status|tinyint(4)|Yes|1
|claimed_days|int(11)|Yes|0
== Table structure for table vip

|------
|Column|Type|Null|Default
|------
|//**id**//|int(11)|No|
|name|varchar(50)|Yes|NULL
|daily_tasks|int(11)|Yes|NULL
|simple_interest|decimal(10,2)|Yes|NULL
|daily_profit|decimal(10,2)|Yes|NULL
|total_profit|decimal(10,2)|Yes|NULL
|activation_fee|decimal(10,2)|Yes|NULL
|status|tinyint(4)|Yes|1
|duration_days|int(11)|Yes|30
== Table structure for table vip_claims

|------
|Column|Type|Null|Default
|------
|//**id**//|int(11)|No|
|user_id|int(11)|No|
|vip_id|int(11)|No|
|user_vip_id|int(11)|No|
|days_claimed|int(11)|Yes|0
|profit|decimal(12,2)|Yes|0.00
|claimed_at|datetime|Yes|current_timestamp()
== Table structure for table withdrawals

|------
|Column|Type|Null|Default
|------
|//**id**//|int(11)|No|
|user_id|int(11)|Yes|NULL
|method|varchar(50)|Yes|NULL
|currency|varchar(10)|Yes|NULL
|amount|decimal(10,2)|Yes|NULL
|address|varchar(255)|Yes|NULL
|fee|decimal(10,2)|Yes|NULL
|received|decimal(10,2)|Yes|NULL
|status|int(11)|Yes|0
|created_at|timestamp|Yes|current_timestamp()
|network_bank|varchar(150)|Yes|NULL
|account_name|varchar(150)|Yes|NULL
|account_number|varchar(150)|Yes|NULL
