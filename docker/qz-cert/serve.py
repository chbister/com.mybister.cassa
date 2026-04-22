import sys
import os
from http.server import BaseHTTPRequestHandler, HTTPServer

class FileHandler(BaseHTTPRequestHandler):
    file_path = None

    def do_GET(self):
        if not os.path.isfile(self.file_path):
            self.send_response(404)
            self.end_headers()
            self.wfile.write(b'File not found')
            return

        try:
            with open(self.file_path, 'rb') as f:
                content = f.read()

            self.send_response(200)
            self.send_header('Content-Type', 'application/octet-stream')
            self.send_header('Content-Length', str(len(content)))
            self.send_header(
                'Content-Disposition',
                f'inline; filename="{os.path.basename(self.file_path)}"'
            )
            self.end_headers()

            self.wfile.write(content)

        except Exception as e:
            self.send_response(500)
            self.end_headers()
            self.wfile.write(str(e).encode())


def run(file_path, host='0.0.0.0', port=8080):
    FileHandler.file_path = file_path
    server = HTTPServer((host, port), FileHandler)

    print(f"Serving {file_path} at http://{host}:{port}")
    server.serve_forever()


if __name__ == '__main__':
    if len(sys.argv) != 2:
        print("Usage: python foobar.py <path-to-file>")
        sys.exit(1)

    file_path = sys.argv[1]

    if not os.path.isfile(file_path):
        print(f"Error: File not found: {file_path}")
        sys.exit(1)

    run(file_path)
