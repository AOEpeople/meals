FROM alpine
ARG K8S_VERSION=v1.8.5
RUN set -x && \
    apk --update upgrade && \
    apk add --no-cache ca-certificates curl openssh && \
    rm -rf /var/cache/apk/* && \
    wget -O /usr/local/bin/kubectl https://storage.googleapis.com/kubernetes-release/release/$K8S_VERSION/bin/linux/amd64/kubectl && \
    chmod +x /usr/local/bin/kubectl
