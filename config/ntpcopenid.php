<?php

/**
 * required 各欄位意義：
 * 'namePerson/friendly', //暱稱
 * 'contact/email', //公務信箱
 * 'namePerson', //姓名
 * 'birthDate', //出生年月日，如： 1973-01-16
 * 'person/gender', //性別
 * 'contact/postalCode/home', //識別碼
 * 'contact/country/home', //單位（學校名），如：育林國中
 * 'pref/language', //年級班級座號 6 碼
 * 'pref/timezone' // 授權資訊[學校別、身分別、職稱別、職務別]，例如：
 * [{"id":"014569","name":"新北市立育林國民中學","role":"教師","title":"專任教師","groups":["科任教師"]}]
 */

return [
    'identity' => 'https://openid.ntpc.edu.tw/',
	'required' => [
        'contact/email', //公務信箱
        'namePerson',    //姓名
        'pref/timezone'  // 授權資訊[學校別、身分別、職稱別、職務別]
    ]
];