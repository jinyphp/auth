<?php
return [
"updated_at"=>"2022-08-19 08:00:22",
"description"=>"\\u3147\\u313b\\u3134\\u3147\\u313b\\u3134\\u3139",
"auth"=>[
"enable"=>true,
"auto"=>true
],
"register"=>true,
"agreement"=>true,
"view"=>[
"register"=>"jinyauth::register",
"agreement"=>"jinyauth::agreement",
"forget"=>"jinyauth::forgot-password"
],
"login"=>true,
"view_login"=>"jinyauth::login",
"logout"=>"\\\/",
"dashboard"=>"\\\/"
];