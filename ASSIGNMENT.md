# Overview

You are tasked with building the backend of a simple reminders application. Front-end web and mobile applications will interact with your backend to allow users to schedule new reminders, view existing reminders, and modify/delete existing reminders. Reminders will be set up on a recurring basis - for example, “Wake up: every morning at 8am”, or “Lunch meeting: every Monday at 12:30pm”.

## Requirements

The structure of your application should include:

- A domain model for representing reminders and their recurrence rules
- A RESTful web service for use by front-end applications
- A mechanism for persisting and querying these structures (see starter project notes)

The following minimum functionality should be included:

- Create, update, and delete reminders and their recurrence rules
- View a list of reminders that will occur in a given date range, based on their recurrence rules
- Search for reminders based on a keyword
- Allow for multiple types of recurrence rules, for example “every day”, “every n days”, and “every Monday”

## Questions

As you work on your solution, you may think of questions that we are not able to answer in real time. Please add these questions to a new file named `QUESTIONS.md` and submit with your solution. Even though we will not be able to answer these questions prior to submission, they will help us frame & evaluate your solution.

Please limit the time you spend working on this project to 4 hours. We are not expecting a complete solution and are interested to see how you prioritise your implementation given the time constraint. Anything that you were not able to get to can be documented in a new file named `TODO.md` to outline the additional changes you would have made with more time on task.

## Starter Project

We have provided a very simple Laravel starter application for this project if you choose to use it, housed inside of the `/laravel-starter` directory.

- This project defines a docker-compose stack consisting of two containers: one running the application, and the other running a web server (NGINX)
- The database is handled through an SQLite file (created in the Setup section)
- HTTP Routes for the project can be defined in the source/routes/api.php file
- We have provided a very simple User model class along with some endpoints for fetching & creating new users. These files can be found in the source/app/Models and source/app/Controllers directories respectively.

To get started / up and running with the laravel starter project, head [here](LARAVEL-STARTER.md).
