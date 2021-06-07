<title>Weather Forecasting App</title>
<style>
    :root {
        --bg_main: #0a1f44;
        --text_light: #fff;
        --text_med: #53627c;
        --text_dark: #1e2432;
        --red: #ff1e42;
        --darkred: #c3112d;
        --orange: #ff8c00;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-weight: normal;
    }

    button {
        cursor: pointer;
    }

    input {
        -webkit-appearance: none;
    }

    button,
    input {
        border: none;
        background: none;
        outline: none;
        color: inherit;
    }

    img {
        display: block;
        max-width: 100%;
        height: auto;
    }

    ul {
        list-style: none;
    }

    body {
        font: 1rem/1.3 "Roboto", sans-serif;
        background: var(--bg_main);
        color: var(--text_dark);
        padding: 50px;
    }

    /*CUSTOM VARIABLES HERE*/

    .top-banner {
        color: var(--text_light);
    }

    .heading {
        font-weight: bold;
        font-size: 4rem;
        letter-spacing: 0.02em;
        padding: 0 0 30px 0;
    }

    .top-banner form {
        position: relative;
        display: flex;
        align-items: center;
    }

    .top-banner form input {
        font-size: 2rem;
        height: 40px;
        padding: 5px 5px 10px;
        border-bottom: 1px solid;
    }

    .top-banner form input::placeholder {
        color: currentColor;
    }

    .top-banner form button {
        font-size: 1rem;
        font-weight: bold;
        letter-spacing: 0.1em;
        padding: 15px 20px;
        margin-left: 15px;
        border-radius: 5px;
        background: var(--red);
        transition: background 0.3s ease-in-out;
    }

    .top-banner form button:hover {
        background: var(--darkred);
    }

    .top-banner form .msg {
        position: absolute;
        bottom: -40px;
        left: 0;
        max-width: 450px;
        min-height: 40px;
    }

    @media screen and (max-width: 700px) {
        .top-banner form {
            flex-direction: column;
        }

        .top-banner form input,
        .top-banner form button {
            width: 100%;
        }

        .top-banner form button {
            margin: 20px 0 0 0;
        }

        .top-banner form .msg {
            position: static;
            max-width: none;
            min-height: 0;
            margin-top: 10px;
        }
    }

    .ajax-section {
        margin: 50px 0 20px;
    }

    .ajax-section .cities {
        display: grid;
        grid-gap: 32px 20px;
        grid-template-columns: repeat(4, 1fr);
    }

    @media screen and (max-width: 1000px) {
        .ajax-section .cities {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media screen and (max-width: 700px) {
        .ajax-section .cities {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media screen and (max-width: 500px) {
        .ajax-section .cities {
            grid-template-columns: repeat(1, 1fr);
        }
    }

    /*CUSTOM VARIABLES HERE*/

    .ajax-section .city {
        position: relative;
        padding: 40px 10%;
        border-radius: 20px;
        background: var(--text_light);
        color: var(--text_med);
    }

    .ajax-section .city::after {
        content: ’’;
        width: 90%;
        height: 50px;
        position: absolute;
        bottom: -12px;
        left: 5%;
        z-index: -1;
        opacity: 0.3;
        border-radius: 20px;
        background: var(--text_light);
    }

    .ajax-section figcaption {
        margin-top: 10px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .ajax-section .city-temp {
        font-size: 5rem;
        font-weight: bold;
        margin-top: 10px;
        color: var(--text_dark);
    }

    .ajax-section .city sup {
        font-size: 0.5em;
    }

    .ajax-section .city-name sup {
        padding: 0.2em 0.6em;
        border-radius: 30px;
        color: var(--text_light);
        background: var(--orange);
    }

    .ajax-section .city-icon {
        margin-top: 10px;
        width: 100px;
        height: 100px;
    }

    a {
        color: var(--text_light);
        font-weight: bold;
    }

</style>
<section class="top-banner">
    <div class="container">
        <h1 class="heading">Simple Weather App</h1>
        <form>
            @csrf
            <input type="text" id='cityId' placeholder="Search for a city" autofocus>
            <button type="submit">SUBMIT</button>
            <a href='/city/noWeatherInfo'><button type='button'>View No weather city list</button></a>
            <span id="msg" class="msg"></span>
        </form>
    </div>
</section>
<section class="ajax-section">
    <div class="container">
        <ul id='list' class="cities"></ul>
    </div>
</section>

<script>
    const form = document.querySelector(".top-banner form");

    form.addEventListener("submit", e => {
        e.preventDefault();
        const inputVal = cityId.value;
        fetch('/city/' + inputVal)
            .then(response => response.json())
            .then(data => {
                // do stuff with the data
                const {
                    main,
                    name,
                    sys,
                    weather
                } = data;
                const icon = `https://openweathermap.org/img/wn/${weather[0]["icon"]}@2x.png`;

                const li = document.createElement("li");
                li.classList.add("city");
                const markup = `
                    <h2 class="city-name" data-name="${name},${sys.country}">
                        <span>${name}</span>
                        <sup>${sys.country}</sup>
                    </h2>
                    <div class="city-temp">${Math.round(main.temp)}<sup>°C</sup>
                    </div>
                    <figure>
                        <img class="city-icon" src=${icon} alt=${weather[0]["main"]}>
                        <figcaption>${weather[0]["description"]}</figcaption>
                    </figure>
                    `;
                li.innerHTML = markup;
                list.insertBefore(li, list.firstChild);
                msg.textContent = "";
                form.reset();
                cityId.focus();
            })
            .catch((e) => {
                console.error(e);
                msg.textContent = "Please search for a valid city 😩";
            });
    });

</script>
<a
    href="https://www.digitalocean.com/?refcode=f5071ab848df&utm_campaign=Referral_Invite&utm_medium=Referral_Program&utm_source=badge"><img
        src="https://web-platforms.sfo2.digitaloceanspaces.com/WWW/Badge%202.svg"
        alt="DigitalOcean Referral Badge" /></a>
