FROM wordpress:6.9.0-php8.2-apache
RUN touch ~/.bashrc && chmod +x ~/.bashrc
SHELL ["/bin/bash", "--login", "-c"]
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.38.0/install.sh | bash
RUN . ~/.nvm/nvm.sh && source ~/.bashrc && nvm install --lts node npm