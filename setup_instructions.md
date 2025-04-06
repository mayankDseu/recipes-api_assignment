# Clone the repo
git clone https://github.com/mayankDseu/recipes-api_assignment.git
cd recipes-api_assignment-master

# Start Docker containers (and make sure docker is installed in your System)
docker-compose up --build

# Install PHP dependencies
docker-compose exec app composer install

# Access the API
http://localhost:8080/

# Enter the Postgres container
docker-compose exec db psql -U user -d hellofresh

1. For Creation of Table hit on Create endpoint using the url given below and it will automaticaly create the table if table not exsist.
2. After that to check wheter realtion is creation use this command:
#  docker exec -it recipes-api_assignment-master-postgres-1 psql -U postgres -d hellofresh

# once inside:
-- See tables
\dt

-- View data from your recipes table 
SELECT * FROM recipes;

-- Exit
\q


##### Recipes Endpoints

| Name   | Method      | URL                    | Protected |
| ---    | ---         | ---                    | ---       |
| List   | `GET`       | `/recipes`             | ✘         |
| Create | `POST`      | `/recipes`             | ✓         |
| Get    | `GET`       | `/recipes/{id}`        | ✘         |
| Update | `PUT/PATCH` | `/recipes/{id}`        | ✓         |
| Delete | `DELETE`    | `/recipes/{id}`        | ✓         |
| Rate   | `POST`      | `/recipes/{id}/rating` | ✘         |
| Search | `GET`       |`/recipes/search?query=` | ✘        |