- intstall :
`composer require gfl/read-docx` 
- using :
```
ReadDocx::loadFile($_FILES['file'])->getIndex()->toJson(); // return json

ReadDocx::loadFile($_FILES['file'])->getIndex()->toHtml(); // return html
 
``` 

- demo :
 http://titword.000webhostapp.com/