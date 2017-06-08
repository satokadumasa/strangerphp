<h1>Auth create<br /></h1>
<form action="<!----value:document_root---->Auth/save/" method="post">
  Auth username<input type="text" name="User[username]" length="64" value="<!----value:User:username---->"><br>
  Auth password<input type="text" name="User[password]" length="64" value=""><br>
  Auth role_id<input type="text" name="User[role_id]" length="64" value="<!----value:User:role_id---->"><br>
  Auth email<input type="text" name="User[email]" length="128" value="<!----value:User:email---->"><br>
  Auth notified_at<input type="text" name="User[notified_at]" length="64" value="<!----value:User:notified_at---->"><br>
  Auth authentication_key<input type="text" name="User[authentication_key]" length="128" value="<!----value:User:authentication_key---->"><br>
  <input type="submit" name="bottom">
</form>
