<h1>User create<br /></h1>
<form action="/User/save/" method="post">
  User user_name<input type="text" name="User[user_name]" length="64" value="<!----value:User:user_name---->"><br>
  User password<input type="text" name="User[password]" length="64" value="<!----value:User:password---->"><br>
  User role_id<input type="text" name="User[role_id]" length="8" value="<!----value:User:role_id---->"><br>
  User delete_flag<input type="text" name="User[delete_flag]" length="1" value="<!----value:User:delete_flag---->"><br>
  <input type="submit" name="bottom">
</form>
