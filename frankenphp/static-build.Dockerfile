FROM --platform=linux/amd64 dunglas/frankenphp:static-builder

# Copy your app
WORKDIR /go/src/app/dist/app
COPY . .

# Build the static binary
WORKDIR /go/src/app/
RUN EMBED=dist/app/ ./build-static.sh