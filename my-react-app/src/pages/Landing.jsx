function LandingPage({onLogin, onRegister}){
    return(
       
        <div className="registerOrLogin">
            {/*ROUGH NAVBAR TO UPDATE IF NEEDED*/}
            <nav className="navbar">
                <span className='nav-logo'>Meet Me @ McGill</span>
                <div className="nav-links">
                <button onClick={onGoLogin}>Login</button>
                <button onClick={onGoRegister} className="btn-primary">Register</button>
                </div>
            </nav>

        {/*Description */}
        <section className = "app_description">
           <h1>Book Appointments with your professor</h1>
           <p>No more back-and-forth emails. Find a slot and book it in seconds.</p>
           <div className="landing_buttons">
                <button onClick={onRegister} className="btn-primary">Get Started</button>
                <button onClick={onLogin}>I already have an account</button>
            </div>
        </section>
        {/*INSTRUCTIONS - HOW It WORKS */}
                                            {/*CAN MAKE IT LIKE HERO CARDS */}
        <section className="how-it-works">
            <h2>How it works</h2>
            <div className='card-row'>
                <div className='cards'>
                    <span className='card-icon'>"emoji"</span>
                    <h3>Professors post slots</h3>
                    <p>Owners set their available hours or meeting times.</p>
                </div>

                <div className="cards">
                    <span className='card-icon'>"emoji"</span>
                    <h3>you browse and book</h3>
                    <p>Find your Professor or TA and reserve an open slot instantly.</p>
                </div>

                <div className="cards">
                    <span className='card-icon'>"emoji"</span>
                    <h3>Both sides confirmed</h3>
                    <p>The appointment shows up on your dashbpoard and you receive an email.</p>
                </div>
            </div>
        </section>
      


        {/*Footer*/}
        <footer className="footer">
            <p>McGill University — SOCS Booking App</p>
        </footer>
        </div>
        

    )


}
export default LandingPage