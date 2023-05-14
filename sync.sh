#!/bin/bash

rsync -avr --delete --exclude-from 'exclude.txt' --progress -e 'ssh -p 65002' /home/jay/Dev/blog/ u156512501@api.designly.biz:/home/u156512501/domains/blog.designly.biz