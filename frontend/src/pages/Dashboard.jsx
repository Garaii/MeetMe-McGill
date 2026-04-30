import { useState, useEffect } from 'react'
import Navbar from '../components/Navbar'
import {apiGet, apiPost} from '../api'

// Dashboard.jsx
function DashboardPage({user, onLogout /*, onBook*/}) {
  const [view, setView] = useState("appointments")
  const [selectedOwner, setSelectedOwner] = useState(null)

   // My CURRENT bookings
  const [bookings, setBookings] = useState([])
  const [bookingsLoading, setBookingsLoading] = useState(true)
  const [bookingsError, setBookingsError] = useState("")
 
  // Browse owners FOR OPEN SLOTS
  const [owners, setOwners] = useState([])
  const [ownersLoading, setOwnersLoading] = useState(false)
  const [ownersLoaded, setOwnersLoaded] = useState(false)
 
  // Owner slots (book view)
  const [ownerSlots, setOwnerSlots] = useState([])
  const [ownerSlotsLoading, setOwnerSlotsLoading] = useState(false)
  const [bookMessage, setBookMessage] = useState("")
  const [bookError, setBookError] = useState(false)

  //REQUEST MEETING
  const [requestOwners, setRequestOwners] = useState([])
  const [requestOwnerId, setRequestOwnerId] = useState("")
  const [requestDate, setRequestDate] = useState("")
  const [requestStartTime, setRequestStartTime] = useState("")
  const [requestEndTime, setRequestEndTime] = useState("")
  const [requestMessage, setRequestMessage] = useState("")
  const [requestError, setRequestError] = useState("")
  const [requestSuccess, setRequestSuccess] = useState("")
  const [requestLoading, setRequestLoading] = useState(false)


  //GROUP MEETING
  const [groupMeetingId, setGroupMeetingId] = useState("")
  const [groupMeeting, setGroupMeeting] = useState(null)
  const [groupOptions, setGroupOptions] = useState([])
  const [selectedOptions, setSelectedOptions] = useState([])
  const [groupLoading, setGroupLoading] = useState(false)
  const [groupError, setGroupError] = useState("")
  const [groupSuccess, setGroupSuccess] = useState("")
  const [groupFetchError, setGroupFetchError] = useState("")

  /* ===================== fething bookings ====================== */
  useEffect(()=>{
    apiGet("dashboard.php")
      .then(data => {
        setBookings(data.user_bookings || [])
        setBookingsLoading(false)
      })

      .catch(err => {
        setBookingsError(err.message)
        setBookingsLoading(false)
      })

  },[])

  /* ======================== FETCH OWNERS LIST (BUT LOADED LAZY SO CHANGE IT)====================== */
  const fetchOwners = () => {
    if (ownersLoaded) return
    setOwnersLoading(true)
    apiGet("owners_list.php")
      .then(data => {
        setOwners(data.owners || [])
        setOwnersLoading(false)
        setOwnersLoaded(true)
      })
      .catch(() => setOwnersLoading(false))

  }

  /*=================__________________Fetch owner for request_________________===============*/
  const fetchRequestOwners = () => {
    if(ownersLoaded){
      setRequestOwners(owners)
      return
    }
    apiGet("owners_list.php")
        .then(data => {
          setRequestOwners(data.owners || [])
          setOwners(data.owners || [])
          setOwnersLoaded(true)
        })
        .catch(() => {})
  }

/* ++++++++++++++++++++++++++++++++ FETCH SLOTS FROM SELECTED OWNER ++++++++++++++++++++++++++++++++++++ */
  const handleSelectOwner = (owner) => {
    setSelectedOwner(owner)
    setOwnerSlots([])
    setBookMessage("")
    setBookError(false)
    setOwnerSlotsLoading(true)
    setView("book")

    apiGet(`owner_booking_page.php?owner_id=${owner.id}`)
      .then(data => {
        setOwnerSlots(data.slots || [])
        setOwnerSlotsLoading(false)
      })

      .catch(() => setOwnerSlotsLoading(false))

  }

  /* _________________________________________ BOOK SLOT FUNCTION _______________________________________________ */

  const handleBookSlot = async (slot_id) => {
    setBookMessage("")
    setBookError(false)
    try{
      //WILL HAVE TO CHANGE NAME WHEN PHP FINISH
      await apiPost("book_slot.php", {slot_id})
      setBookMessage("Slot booked sucessfully!")
      setBookError(false)
      setOwnerSlots(prev => prev.filter (s => s.id !== slot_id))

      //making sure to update the bookings list
      apiGet("dashboard.php").then( d => setBookings(d.user_bookings || []))
    } catch (err) {
      setBookMessage(err.message)
      setBookError(true)
    }
  }

  /* _______________________________________ CANCELING BOOKING FUNCTION __________________________________ */
  const handleCancelBooking = async (booking) => {
    if (booking.can_cancel === 0 || booking.can_cancel === "0") return

    // will have to update appearance of this
    if(!window.confirm("Cancel this booking?")) return
    try {
      const data = await apiPost("cancel_booking.php", { booking_id: booking.booking_id})

      if(data.owner_email){
          const body = encodeURIComponent(
        `Hi ${data.owner_name || ''},\n\nThis is to let you know that ${user.name} has cancelled their booking.\n\nThe slot is now available again.\n\nThank you!`
      )
      const subject = encodeURIComponent('Booking Cancelled')
      window.location.href = `mailto:${data.owner_email}?subject=${subject}&body=${body}`

      }

      setBookings( prev => prev.filter (b=> b.booking_id !== booking.booking_id))

    } catch (err) {
      alert(err.message)
    }
  }

  /* ================__________________________ TO SEND MEETIJNG REQUEST ______________________________========== */
  const handleSendRequest = async () => {
    setRequestError("")
    setRequestSuccess("")
    if (!requestOwnerId || !requestDate || !requestStartTime || !requestEndTime || requestMessage.trim() === "") {
      setRequestError("Please select an owner, suggest a time, and write a message.")
      return
    }

    if (requestEndTime <= requestStartTime) {
      setRequestError("End time must be later than start time.")
      return
    }
    setRequestLoading(true)
    try {
      //WILL HAVE TO update endpoint name request_meeting.php
      await apiPost("request_meeting.php", {
        owner_id: requestOwnerId,
        message: requestMessage,
        requested_date: requestDate,
        start_time: requestStartTime,
        end_time: requestEndTime
      })

      setRequestSuccess("Meeting request sent successfully!")
      setRequestOwnerId("")
      setRequestDate("")
      setRequestStartTime("")
      setRequestEndTime("")
      setRequestMessage("")
    } catch (err) {
      setRequestError(err.message)
    } finally {
      setRequestLoading(false)
    }
  }

/* t2+++++++++++++++++++++++----------- LOAD GROUP ---------+++++++++++++++++++++ */
const handleLoadGroupMeeting = async () => {
    setGroupFetchError("")
    setGroupMeeting(null)
    setGroupOptions([])
    setSelectedOptions([])
    setGroupSuccess("")
 
    if (!groupMeetingId.trim()) {
      setGroupFetchError("Please enter a meeting ID.")
      return
    }
 
    try { // WILL HATE TO UPDATE
      const data = await apiGet(`submit_group_availability.php?group_meeting_id=${groupMeetingId}`)
      setGroupMeeting(data.meeting)
      setGroupOptions(data.options || [])
    } catch (err) {
      setGroupFetchError(err.message)
    }
  }
  /*T2 ++++++++++++++++++++++++++++++_______OPTIONS SELECTION_______++++++++++++++++++++++++++++++++ */
    const handleToggleOption = (option_id) => {
    setSelectedOptions(prev =>
      prev.includes(option_id)
        ? prev.filter(id => id !== option_id)
        : [...prev, option_id]
    )
    }

  /*T2 ________________AVAILABILITY____________________ */
   const handleSubmitAvailability = async () => {
    setGroupError("")
    setGroupSuccess("")
 
    if (selectedOptions.length === 0) {
      setGroupError("Please select at least one option.")
      return
    }
 
    setGroupLoading(true)
    try { //WILL HAVE TO UPDATE
      const data = await apiPost("submit_group_availability.php", {
        group_meeting_id: groupMeetingId,
        selected_options: selectedOptions
      })
      setGroupSuccess(data.message)
      setSelectedOptions([])
    } catch (err) {
      setGroupError(err.message)
    } finally {
      setGroupLoading(false)
    }
  }
  /* ______________________ GROUPING BOOKINGS ___________________ */
  const groupedBookings = {
  group: bookings.filter(b => b.slot_type === 'group'),
  manual: bookings.filter(b => b.slot_type === 'manual')
  }



  /* &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&_______________________ ui __________________&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& */
  return (
  <div>
    <Navbar onLogout={onLogout} user={user} />
    <div className="dashboard">
    <h1>Welcome back, {user.name}! </h1>
    {/*LISTING TO SEE @MCGILL.CA WHO HAVE ACTIVE SLOTS
    * SELECT OWNER THEN SEE THEIR AVAILABLE SLOTS
    * AS A USER YOU CAN BOOK A SLOT
    * 
    * SEE THE SLOTS YOU HAVE BOOKED
    * -DELETE --> SLOT OWNER RECEIVE NOTIFICATION ':mailto'
    * -> slot goes back to available
    * - MESSAGE THE OWNER OF THE SLOT
    * 
    * Users see a dashboard listing their appointments and providing the following options:
    ▪ They can email (mailto:) the owner of a slot (selected or open slot)
    ▪ They can delete appointments.
    ▪ They can book additional appointments.
    ▪ They can logout
    * 
    * 
    */}
    {/* INSTEAD OF HAVING DIFFERENT PAGES WILL HAVE TABLES INSIDE OF DASHBOARD*/}
    
    {/* ................... TABS ..................*/}       
    
    <div className="dashboard-tabs">
      <button 
        className="appt-tab-btn"
        onClick={() => setView("appointments")}
      >
        My Appointments
      </button>
      <button 
        className="browse-tab-btn" 
        onClick={() => {setView("browse"); fetchOwners()}}
      >
        Browse Owners
      </button>
      <button 
        className='request-tab-btn'
        onClick={() => { setView("request"); fetchRequestOwners() }}
      >
        Request a Meeting
      </button>
      <button className='submit-tab-btn' onClick={() => setView("group_vote")}>
        Submit Availability
      </button>
    </div>
     
    {/* ___________________________ MY APPOINTMENTS _______________________*/}
    {view === "appointments" && (
      <section className='appointments-view'>
        <h2>My Appointments</h2>
          
        {/* appointment block with
            ACTIONS: 
            - mailto owner of slot
            - Delete
            DETAILS: TIMES, DATE, OWNER NAME, 2 BUTTONS FROM THE ACTIONS ABOVE
            THIS IS FOR EXISTING APPOINTMENTS, HAVE TO MAP THEM TO BACK END
        */}

        {/* fetching for bookings, loading */}
        {bookingsLoading && <p> Loading... </p>}
        {bookingsError && <p className='auth-error'>{bookingsError}</p>}

        {/* if no bookings found we tell student they have none */}
        {!bookingsLoading && bookings.length === 0 && (
          <p>No appointments yet.</p>
        )} 

        {/* HERE TO DISPLAY BOOKINGS AS A TABLE, WILL HAVE TO STYLE */}
        {!bookingsLoading && bookings.length > 0 && (
          <>
            <h3 style={{ marginTop: '16px', marginBottom: '8px', fontSize: '15px', color: 'var(--text-muted)' }}>
              Booked Appointments
            </h3>
            <table className='dashboard-table'>
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Start</th>
                  <th>End</th>
                  <th>Owner</th> 
                  <th>Location</th>
                  <th>Actions</th>
                </tr>
              </thead>

              <tbody>
                {/*.sort((a, b) => new Date(a.slot_date) - new Date(b.slot_date)) */}
                {groupedBookings.manual.map(booking => (
                  <tr key={booking.booking_id}>
                    <td>{booking.slot_date}</td>
                    <td>{booking.start_time}</td>
                    <td>{booking.end_time}</td>
                    <td>{booking.owner_name}</td>
                    <td>{booking.location || "Not specified"}</td>

                    <td>
                      <a href={`mailto:${booking.owner_email}`}>
                        <button>Email Owner</button>
                      </a>
                      {(booking.can_cancel !== 0 && booking.can_cancel !== "0") && (
                        <button className='cancel-btn' onClick={()=> handleCancelBooking(booking)}>
                          Cancel
                        </button>
                      )}

                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </>
        )}

        {!bookingsLoading && groupedBookings.group.length > 0 && (
        <>
          <h3 style={{ marginTop: '24px', marginBottom: '8px', fontSize: '15px', color: 'var(--text-muted)' }}>
            Group Meeting Appointments
          </h3>
          <table className="dashboard-table">
            <thead>
              <tr><th>Title</th><th>Date</th><th>Start</th><th>End</th><th>Owner</th><th>Location</th><th>Actions</th></tr>
            </thead>
            <tbody>
              {groupedBookings.group.map(booking => (
                <tr key={booking.booking_id}>
                  <td>{booking.title}</td>
                  <td>{booking.slot_date}</td>
                  <td>{booking.start_time}</td>
                  <td>{booking.end_time}</td>
                  <td>{booking.owner_name}</td>
                  <td>{booking.location || "Not specified"}</td>
                  <td>
                    <a href={`mailto:${booking.owner_email}`}><button>Email Owner</button></a>
                    {(booking.can_cancel !== 0 && booking.can_cancel !== "0") ? (
                      <button className='cancel-btn' onClick={() => handleCancelBooking(booking)}>Cancel</button>
                    ) : (
                      <span>Group meeting</span>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </>
      )}

    </section>
  )}

    {/* NEW APPOINTMENTS ------------- BROWSE OWNERS */}
    {view === "browse" && (
        <section className="browse-view">
          <h2>Browse Owners</h2>
          {/* fetch list of @mcgill.ca owners with active slots */}
          {/* DISPLAY AS ROW OR CARDS: owner name, num slots, button to view their slots */}
          {ownersLoading && <p>Loading....</p>}

          {/* _________________ no owners with slots open _______________*/}
          {!ownersLoading && ownersLoaded && owners.length === 0 && (
            <p>No one with active slots right now.</p>
          )}

          {/* _______________________________________ */}
          {!ownersLoading && owners.length > 0 && (
            <div className="owners-list">
              {owners.map(owner => (
                <div key={owner.id} className='owner-card'>
                  <div>
                    <strong>{owner.name}</strong>
                    <span>{owner.email}</span>
                </div>
                <button
                  className='btn-primary'
                  onClick={() => handleSelectOwner(owner)}
                >
                  View Slots
                </button>
                </div>

              ))}
            </div>
          )}
        </section>
    )}

    {/* we did view not we want tto actually book slots */}


    {/* ________________________ BOOK SLOTS ______________________ */}
    {view === "book" && selectedOwner && (
      <section className="book-view">
        
        <button onClick={() => setView("browse")}>← Back to browse</button>
        <h2>Available slots with {selectedOwner.name}</h2>
        
        {/* fetch active slots for this owner FROM BACKEND */}
        {/* each slot: date, time, type, book button */}

        {bookMessage && <p className={bookError ? 'auth-error' : 'form-message'}>{bookMessage}</p>}
        {ownerSlotsLoading && <p>Loading slots...</p>}

        {!ownerSlotsLoading && ownerSlots.length === 0 && (
          <p>No available slots for this person</p>
        )}

        {/*______LOADING TABLE OF OWNER AVAILABILITIES_______ */}
        {!ownerSlotsLoading && ownerSlots.length > 0 && (
          <table className="dashboard-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Location</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  {ownerSlots.map(slot => (
                    <tr key={slot.id}>
                      <td>{slot.slot_date}</td>
                      <td>{slot.start_time}</td>
                      <td>{slot.end_time}</td>
                      <td>{slot.location || "Not specified"}</td>
                      <td>
                        <button
                          className="btn-primary"
                          onClick={() => handleBookSlot(slot.id)}
                        >
                          Book
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
        )}
        </section>
    )}


    {/* T1____________________ STUDENT REQUESTING A MEETING ______________________*/}
    {view === "request" && (
      <div className="auth-page">
        <section className="auth-card">
          <h2>Request a Meeting</h2>
          <p className="auth-subtitle">Send a meeting request to an owner</p>

          {requestError && <p className="auth-error">{requestError}</p>}
          {requestSuccess && <p className="auth-success">{requestSuccess}</p>}

          <div className="auth-form">
            <div className="form-group">
              <label>Select Owner</label>
              <select
                value={requestOwnerId}
                onChange={e => setRequestOwnerId(e.target.value)}
              >
                <option value="">-- Choose an owner --</option>
                {requestOwners.map(owner => (
                  <option key={owner.id} value={owner.id}>
                    {owner.name} ({owner.email})
                  </option>
                ))}
              </select>
            </div>
            <div className="form-group">
              <label>Suggested Date</label>
              <input
                type="date"
                value={requestDate}
                onChange={e => setRequestDate(e.target.value)}
              />
            </div>
            <div className="form-row">
              <div className="form-group">
                <label>Start Time</label>
                <input
                  type="time"
                  value={requestStartTime}
                  onChange={e => setRequestStartTime(e.target.value)}
                />
              </div>
              <div className="form-group">
                <label>End Time</label>
                <input
                  type="time"
                  value={requestEndTime}
                  onChange={e => setRequestEndTime(e.target.value)}
                />
              </div>
            </div>
            <div className="form-group">
              <label>Message</label>
              <textarea
                placeholder="Describe why you'd like to meet..."
                value={requestMessage}
                onChange={e => setRequestMessage(e.target.value)}
                rows={4}
              />
            </div>

            <button
              className="btn-primary btn-full"
              onClick={handleSendRequest}
              disabled={requestLoading}
            >
              {requestLoading ? "Sending..." : "Send Request"}
            </button>
          </div>
        </section>
      </div>
    )}

    {/* T2____________ GROUP AVAILABILITY MEETING ____________ */}
    {view === "group_vote" && (
      <div className="auth-page">
        <section className="auth-card">
          <h2>Submit Availability</h2>
          <p className="auth-subtitle">Enter the meeting ID shared by the owner</p>

          <div className="auth-form">
            <div className="form-group">
              <label>Group Meeting ID</label>
              <input
                type="number"
                placeholder="e.g. 12"
                value={groupMeetingId}
                onChange={e => setGroupMeetingId(e.target.value)}
              />
            </div>
            {groupFetchError && <p className="auth-error">{groupFetchError}</p>}
            <button className="btn-primary btn-full" onClick={handleLoadGroupMeeting}>
              Load Meeting
            </button>
          </div>

          {/* Once meeting is loaded, show options to vote on */}
          {groupMeeting && (
            <div className="auth-form" style={{ marginTop: '1rem' }}>
              <h3>{groupMeeting.title}</h3>
              {groupMeeting.description && <p>{groupMeeting.description}</p>}
              <p>Organized by: <strong>{groupMeeting.owner_name}</strong></p>
              <p>Location: <strong>{groupMeeting.location || "Not specified"}</strong></p>

              <p>Select the times that work for you:</p>
              {groupOptions.map(option => (
                <div key={option.id} className="form-group" style={{ flexDirection: 'row', alignItems: 'center', gap: '0.5rem' }}>
                  <input
                    type="checkbox"
                    id={`opt-${option.id}`}
                    checked={selectedOptions.includes(option.id)}
                    onChange={() => handleToggleOption(option.id)}
                  />
                  <label htmlFor={`opt-${option.id}`}>
                    {option.option_date} — {option.start_time} to {option.end_time}
                  </label>
                </div>
              ))}

              {groupError && <p className="auth-error">{groupError}</p>}
              {groupSuccess && <p className="auth-success">{groupSuccess}</p>}

              <button className="btn-primary btn-full" onClick={handleSubmitAvailability} disabled={groupLoading}>
                {groupLoading ? "Submitting..." : "Submit Availability"}
              </button>
            </div>
          )}
        </section>
      </div>
    )}
   
    {/* APPOINTMENT ACTIONS*/}
    {/*Footer*/}
    <footer className="footer">
        <p>© 2026 MeetMe @ McGill — SOCS Booking App</p>
    </footer>
       
    </div>
  </div>
    
  )
}
export default DashboardPage
