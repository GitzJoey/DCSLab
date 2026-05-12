# DCSLab

Doctor Computer SG Lab

This project is inspired by the desire to create an up to date web app boilerplate and keep evolving.

A web application focusing on the usage of the most popular framework, handpicked with consideration. To meet the everyday programming/coding obstacle and giving the best solution that we can find.  
Sometimes google/stackoverflow searching is enough to satisfied our curiosity, but complex issue such as inter-related components, multi vendors applications problems, hardware software compatibility, bad/slow performances of projects, is something that you can only face it if you do a real projects.  

This project is to 'simulate' the everyday production worthy of obstacles.
Interested? let discuss it together [here](https://github.com/GitzJoey/DCSLab/discussions)

## Features
* Role Based System
* Internal Messaging System
* Auditing Tools
* Multi language
* Single Page Application
* Secure Coding Paradigm
* and more...

## Installation
* Create the docker images for web and api 
  ```
  $ docker build -f Dockerfile-Web -t dcslab/web:latest .
  $ docker build -f Dockerfile-API -t dcslab/api:latest .
  ```
* Create the containers
  ```
  $ docker compose up -d
  ```

## Change Logs

Full change logs can be found [here](CHANGELOG.md)

## License
[MIT License](http://opensource.org/licenses/MIT)  
The MIT License is a permissive license.  
It lets you do anything with this code as long as you provide **attribution back to this website**. 

## Treat Us Coffee

If you think this open source project usefull

<a href="https://www.buymeacoffee.com/gitzjoey" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-blue.png" alt="Buy Me A Coffee" style="height: 60px !important;width: 217px !important;" ></a>
