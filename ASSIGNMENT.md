# Reminder Scheduling

## Scenario

A colleague of yours started working on a new application that allows users to schedule different types of reminders. The aim of this application is to provide users the ability to see their upcoming reminders for a provided date range. Your colleague has gone out on leave about halfway through the project, handing off the responsibility of finishing the project to you. While they did not leave you any syntax errors, you notice that there is no entrypoint to the project in it's current state, rather just class files that are not called from anywhere.

## Assignment

We are looking for you to think through and implement the modifications and additions required to solve the problem:

- There is no entrypoint for the application at the moment. We are looking for you to add a way to create and view scheduled reminders, preferrably over HTTP.
- We have provided a Laravel starter project as this is the framework we use at the moment. You are not required to use Laravel or PHP for this project.
- There is no particular structure required for this project. Make the changes and additions that you feel best fit the project.

Please limit the time you spend working on this project to 4 hours. We are not expecting a complete solution and are interested to see how you prioritise your implementation given the time constraint. Anything that you were not able to get to can be documented in a new file named `TODO.md` to outline the additional changes you would have made with more time on task.

## Questions

As you work on your solution, you may think of questions that we are not able to answer in real time. Please add these questions to a new file named `QUESTIONS.md` and submit with your solution. Even though we will not be able to answer these questions prior to submission, they will help us frame & evaluate your solution.

## Laravel Starter

If you decide to use our provided laravel starter project, here are a couple details that may help you get up and running:

- Define any HTTP routes you intend to use inside of the `routes/api.php` file. You do not need to worry about user authentication and access
- We have provided a makefile that defines the following actions:
| command    | Description                                                       |
|------------|-------------------------------------------------------------------|
| make build | Build the images defined in the compose file provided             |
| make up    | Starts the docker containers defined in the compose file provided |
| make stop  | Stops the containers defined in the compose file provided         |
| make down  | Removes the containers defined in the compose file provided       |
- The application will run at `http://localhost:8080`
