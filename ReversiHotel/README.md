### Struktur Folder
```
/src
  /components
    Navbar.js
    Footer.js
  App.js
```

### Contoh Kode Komponen

**1. Navbar.js**
```jsx
import React from 'react';

const Navbar = () => {
    return (
        <nav>
            <h1>My Website</h1>
            <ul>
                <li>Home</li>
                <li>About</li>
                <li>Contact</li>
            </ul>
        </nav>
    );
};

export default Navbar;
```

**2. Footer.js**
```jsx
import React from 'react';

const Footer = () => {
    return (
        <footer>
            <p>&copy; 2023 My Website</p>
        </footer>
    );
};

export default Footer;
```

### Mengimpor Komponen di App.js

**3. App.js**
```jsx
import React from 'react';
import Navbar from './components/Navbar';
import Footer from './components/Footer';

const App = () => {
    return (
        <div>
            <Navbar />
            <main>
                <h2>Welcome to My Website</h2>
                <p>This is the main content area.</p>
            </main>
            <Footer />
        </div>
    );
};

export default App;
```

### Penjelasan
- **Struktur Folder**: Anda memiliki folder `components` yang menyimpan semua komponen terpisah, yaitu `Navbar` dan `Footer`.
- **Komponen**: Setiap komponen didefinisikan dalam file terpisah, membuat kode lebih terorganisir dan mudah dikelola.
- **Impor**: Di dalam `App.js`, Anda cukup mengimpor `Navbar` dan `Footer` sekali, dan menggunakannya di dalam JSX. Ini mengurangi pengulangan kode dan membuat file `App.js` lebih bersih.

Dengan cara ini, Anda dapat dengan mudah menambahkan lebih banyak komponen di masa depan tanpa membuat file `App.js` menjadi terlalu panjang.