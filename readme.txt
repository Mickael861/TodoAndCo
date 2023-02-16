Github : https://github.com/Mickael861/todoandco.git

1- Create a folder on your desktop

2 - Initialize GIT in folder (git init)

3 - Clone remote repository : $ git clone https://github.com/Mickael861/todoandco.git

4 - Composer install

5 - update file .env.local and .env.test (If necessary)

6 - create a database (doctrine:database:create)

7 - Update the database schema (doctrine:schema:update --force)

8 - Load fixture (doctrine:fixtures:load / doctrine:fixture:load --env=test)


