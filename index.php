<?php

const GROUP_ID = 16581443;

function httpQuery( $url ) {
	return json_decode(file_get_contents( $url ));
}

function dd($array) {
	echo '<pre>';
		print_r($array);
	die('</pre>');
}


//Первые 100 постов
$postsData = httpQuery(
	'https://api.vk.com/method/wall.get'
	.'?owner_id=-'.GROUP_ID
	.'&count=100'
	.'&filter=all'
	.'&extended=1'
);

//Оставшиеся от 2 апреля
$lastPostsData = httpQuery(
	'https://api.vk.com/method/wall.get'
	.'?owner_id=-'.GROUP_ID
	.'&offset=100'
	.'&count=69'
	.'&filter=all'
	.'&extended=1'
);

//Сливаем в один массив
$postsItems = [];
foreach ($postsData->response->wall as $item) {
	$postsItems[] = $item;
} unset($item);

foreach ($lastPostsData->response->wall as $item) {
	$postsItems[] = $item;
} unset($item);

array_reverse($postsItems);

//удаляем первые три элемента массива, они не нужны
unset($postsItems[0], $postsItems[1], $postsItems[2]);

$users = [];

//Собираем информацию о пользователях
foreach ($postsItems as $item) {
	if(isset($item->from_id) && isset($item->id)) {
		$post = 'wall'.$item->from_id.'_'.$item->id;
		$resosts = httpQuery(
			'https://api.vk.com/method/likes.getList'
			.'?type=post'
			.'&owner_id='.$item->from_id
			.'&item_id='.$item->id
			.'&filter=copies'
			.'&friends_only=1'
			.'&extended=1'
		);

		if(count($resosts->response->items)) {
			foreach ($resosts->response->items as $user) {
				if(isset($user->uid)) $users[] = "{$user->uid}|{$post}";
			}
		}
	}
} unset($item);

//Только уникальные пользователи
$uni = array_unique($users);

//Определяем победителя
$winner = $uni[ rand( 0, (count($uni) - 1) ) ];
$winner = explode('|', $winner);

echo 'ID Победителя: ' . $winner[0];
echo '<br>';
echo 'Пост, который он репостнул: ' . $winner[1];
