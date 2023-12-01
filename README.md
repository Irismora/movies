#He realizado este proyecto con Symfony 6 PHP con sistema operativo windows 10 y Xampp para el entorno necesario.

#Esta api realiza 3 acciones contra la DB:
1.POST a la base de datos.
2.GET para obtener listado de películas.
3.GET para obtener los detalles de una película.

#En el archivo .env se debe cambiar la configuración de la DB según su configuración personal.

#Para crear la base de datos ejecutar:
php bin/console doctrine:database:create ( en mi caso se llama symfony_movies)

#Para realizar la migración:
php bin/console make:migration
php bin/console doctrine:migrations:migrate

#He realizado las consultas de las peticiones mediante Postman.
El archivo Movies.postman_collection contiene estas peticiones.

#Para levantar el servidor y poner a la escucha:
symfony server:start --port=8080

