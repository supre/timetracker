## Overview

This app can be accessed at: `https://tracker.a-roar.com`.

---

### Technical stack used

* PHP 7.3
* React 16.31
* MySQL 5.7

---

### Installation & running on dev environment

`Docker` compose has been used to containerize the app. 

Execute *following steps* to run the app on your local environment:
1. Clone the repository in a local folder
2. Run the following command in the root of the folder

```
docker-compose -f docker-compose.yml up
```

This would spawn the three containers for service, ui, & database on your local machine.

3. After the containers are spawned, you'll need to run a migration script before you can access the app.
Run the following command in your cli

```
docker exec -it timetrackr_backend php src/cli.php migrations:migrate -q
```

4. Use your browser to go to: `http://localhost:3000`

---

### Unit Tests

To run backend unit tests, run the following command in your cli

```
docker exec -it timetrackr_backend vendor/bin/codecept run
```

To run frontend unit tests, run the following command in your cli

```
docker exec -it timetrackr_ui npm run test -- --watchAll=false
```

---


### API documentation

API documentation is  [published here](https://documenter.getpostman.com/view/42374/TVRg6pLH).



#### Accessing postman collection

A postman collection and a corresponding environment have been provided in the docs folder. They can be imported in the postman to access the api endpoints.
