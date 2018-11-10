<?php
class FollowHelper{
  public static function followOrNot($friend_ids, $user) {
    if (isset($friend_ids) && in_array($user['User']['id'], $friend_ids)) {
      // フォロー済みの場合 
    $html_str = <<<EOF
<div class="already_followed">
  フォロー中
</div>
EOF;
    }
    else {
      //  フォローしていない場合
    $html_str = <<<EOF
<div class="will_follow">
  フォロー
</div>
EOF;
    }
  }
}