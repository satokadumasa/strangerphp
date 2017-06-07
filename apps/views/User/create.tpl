<h1>User create<br /></h1>
<form action="/User/save/" method="post">
  User username<input type="text" name="User[username]" length="64" value="<!----value:User:username---->"><br>
  User password<input type="text" name="User[password]" length="64" value="<!----value:User:password---->"><br>
  User role_id<input type="text" name="User[role_id]" length="64" value="<!----value:User:role_id---->"><br>
  User email<input type="text" name="User[email]" length="128" value="<!----value:User:email---->"><br>
  User notified_at<input type="text" name="User[notified_at]" length="64" value="<!----value:User:notified_at---->"><br>
  User authentication_key<input type="text" name="User[authentication_key]" length="128" value="<!----value:User:authentication_key---->"><br>
  <input type="submit" name="bottom">
</form>
