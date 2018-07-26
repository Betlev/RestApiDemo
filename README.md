**NOTE: This is a simple application demo and  is not intended for use under any circumstances**


**Usage**

GET: api/twitter/lastTweetsFromUser?username=[user_name]&num_tweets=[num_of_tweets]


**Application overview**

This app is designed as a multiple social media api integration and normalization of extracted data

The application workflow is as follows:
 * A _front controller_ matches url calls of the form "api/{social_media_name}/{social_media_use_case_method}[?method=argument]"
 * The social_media_name and social_media_use_case_method are checked against existing social media api Client implementations
 * If there is a match on both social media name and use case, a new client instance is created, and it's method called
 * The Client checks for possible required arguments, 
 * The Client then performs a Connection to the API, though a ConnectionProvider middleware that acts as a Facade
 of all the Request-Connection-Response procedure, making it loosely coupled to the application logic
 * The Facade fulfills the request data, signs the request if required for authentication and performs the request call
 * Last, the response is given back to the Client, which returns it to the front controller for beign dispatched 
 

**Project structure and file organization:**

As stated by Robert Martin in is talk about clean architecture:

_"Just like seeing a building blueprint tells you what kind of building is (an
 hospital, a library, etc) an application file structure should tell you immediately
 what the application does, or what is its purpose (a booking program for example), 
 rather than telling the concrete implementation (MVC, Repositories..)"_

This application is about requesting social media user information and user published content,
more specifically a Twitter user and tweets.

With this in mind, i've tried to make a file structure that would reflex this fact, leaving three main folders under src
 
 * Application: Where application-wide code ans services would reside. These should be domain-independent.
 * Controller:  Required by the framework. Only the front controller stays in
 * SocialMedia: Domain-specific logic should be put here. contains folders for each social media
                and a "common" for social media wide logic   

For the sake of architecture, i've choose not to use any known Twitter auth library, like
abraham/twitteroauth: otherwise it would be enough with very few lines of code to complete the task, and would render 
this exercise useless.

At the same time, for avoid taking too long to make and not over-engineering it, 
i've left several details like error handling or output formatting a little unhandled.


**Software** 

PHP 7.2-fpm
Nginx 1.10


**Frameworks**
symfony/skeleton (v4.1.1.2)
symfony/phpunit-bridge (v4.1.1)

