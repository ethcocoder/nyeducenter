Step 1: Every HTML document starts with a document type declaration. This is not an HTML tag, but an instruction to the browser about what version of HTML the page is written in.
Type: <!DOCTYPE html>
- match: <!DOCTYPE html>

Step 2: After the declaration, we need the root element that wraps all the content on the entire page. This is the <html> tag.
Type: <html></html>
- match: <html></html>

Step 3: Inside the html tag, we add the <head> element. This is a container for metadata (data about the HTML document) and is not displayed in the browser window.
Type: <head></head>
- match: <head></head>

Step 4: The final required element is the <body>. This contains the document's content that is displayed in the browser, such as text, hyperlinks, images, and tables. Let's add our message.
Type: <body>Hello, World!</body>
- match: <body>Hello, World!</body>