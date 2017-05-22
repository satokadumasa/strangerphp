# strangerphp
This is PHP Web Framework.

# Create new project
$> git clone https://github.com/satokadumasa/strangerphp
$> mv strangerphp new_project_name

Currently, only the following functions can be used.
1. Scaffold
You can create a controller, model, View template file.
2. Generate controller
You can create controller & method, View template file.
3. Generate model class
You can create model classes.

A simple example of use is described below.

# scaffold
By using the Scaffold function, you can create a controller, model, and view template with CRUD function at once.

ex)
$> php ./stranger.php -g scaffold books name:string outline:text detail:text 

# generate controller
If you create only the controller, execute the stranger command as shown.
The controller and the view template file are created.

ex)
$> php ./stranger.php -g controller books index show create delete

#　generate model
If you create only the model class file, execute the stranger command as shown below.

ex)
$> php ./stranger.php -g model books name:string outline:text detail:text

