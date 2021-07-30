FROM alpine

ARG GH_VERSION=1.13.1
ARG K8S_VERSION=v1.8.5

RUN set -x && \
    apk --update upgrade && \
    apk add --no-cache ca-certificates curl git openssh && \
    rm -rf /var/cache/apk/* && \
    wget -O /usr/local/bin/kubectl https://storage.googleapis.com/kubernetes-release/release/$K8S_VERSION/bin/linux/amd64/kubectl && \
    chmod +x /usr/local/bin/kubectl

RUN curl -s -L "https://github.com/cli/cli/releases/download/v${GH_VERSION}/gh_${GH_VERSION}_linux_amd64.tar.gz" | tar xvz -C /tmp && \
    mv "/tmp/gh_${GH_VERSION}_linux_amd64/bin/gh" /usr/local/bin/gh && \
    gh config set prompt disabled && \
    mkdir -p ~/.ssh/ && \
    ssh-keyscan github.com >> ~/.ssh/known_hosts && \
    rm -rf /tmp/*
