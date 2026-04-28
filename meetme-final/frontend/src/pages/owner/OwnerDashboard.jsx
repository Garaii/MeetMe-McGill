/* ********************************************************************************************************* */
// OwnerDashboard.jsx
import { useState, useEffect } from 'react'
import Navbar from '../../components/Navbar'
import {apiGet, apiPost} from '../../api'

function OwnerDashboard({ user, onLogout }) {
  const [view, setView] = useState("slots")

  // Slots state
  const [slots, setSlots] = useState([])
  const [slotsLoading, setSlotsLoading] = useState(true)
 
  // Requests state
  const [requests, setRequests] = useState([])
  const [requestsLoaded, setRequestsLoaded] = useState(false)

  // Create slot form state
  const [slotDate, setSlotDate] = useState('')
  const [startTime, setStartTime] = useState('')
  const [endTime, setEndTime] = useState('')
  const [slotError, setSlotError] = useState('')
  const [slotSuccess, setSlotSuccess] = useState('')
  const [slotLoading, setSlotLoading] = useState(false)

  // Create group meeting form state
  const [meetingTitle, setMeetingTitle] = useState('')
  const [meetingDescription, setMeetingDescription] = useState('')
  const [meetingOptions, setMeetingOptions] = useState([
    { option_date: '', start_time: '', end_time: '' },
    { option_date: '', start_time: '', end_time: '' },
  ])
  const [meetingError, setMeetingError] = useState('')
  const [meetingSuccess, setMeetingSuccess] = useState('')
  const [meetingLoading, setMeetingLoading] = useState(false)

  // FOR GROUP MEETING
  const [groupMeetings, setGroupMeetings] = useState([])
  const [selectedMeeting, setSelectedMeeting] = useState(null)
  const [meetingVotes, setMeetingVotes] = useState([])
  const [votesLoading, setVotesLoading] = useState(false)
  const [finalizeMessage, setFinalizeMessage] = useState('')

  // INVITATION URL STATE
  const [inviteUrl, setInviteUrl] = useState('')
  const [inviteCopied, setInviteCopied] = useState(false)


  // ======================= Fetch slots from php on mount
  useEffect(() => {
    apiGet('owner_slots.php')
      
      .then(data => {
        setSlots(data.slots || [])
        setSlotsLoading(false)
      })
      .catch(() => setSlotsLoading(false))


      //creating URL using curretn user's ID
      //Links to owner's booking page
    const base = window.location.hostname === "localhost"
    ? "http://localhost:5173"
    : window.location.origin
    setInviteUrl(`${base}/book/${user.id}`)


  }, [])

  // ======================== Fetch requests (only when tab opened) 
  const fetchRequests = () => {
    if (requestsLoaded) return
    apiGet('owner_requests.php')
      
      .then(data => {
        setRequests(data.requests || [])
        setRequestsLoaded(true)
        
      })
  }

  // ___________ COPY invite URL to CLipboard
    const handleCopyInvite = () => {
    navigator.clipboard.writeText(inviteUrl).then(() => {
      setInviteCopied(true)
      setTimeout(() => setInviteCopied(false), 2000)
    })
  }

  // Toggle slot active / private 
  const handleToggleVisibility = async (slot_id, current_status) => {
    try{ /*WILL HAVE TO UPDATE ENDPOINT NAME WHEN PHP PAGE BELOW IS DONE */
      await apiPost("update_slot_availability.php",{
      slot_id,
      is_active: current_status ? 0: 1
    })
    
      setSlots(prev =>
        prev.map(s => s.id === slot_id ? { ...s, is_active: current_status ? 0 : 1 } : s)
      )
    } catch (err){
      alert(err.message)
    }
    
  }

  // _____________________________Delete slot , transforming into json
  const handleDeleteSlot = async (slot_id) => {
    if (!window.confirm('Delete this slot?')) return
    try{ //WILL HAVE TO CHANGE NAME WHEN PHP FINISH
      await apiPost("delete_slot.php", {slot_id: slot.id})

      // open mail to to notify slot is booked
      if (slot.booked_by_email) {
        window.location.href = `mailto:${slot.booked_by_email}?subject=Booking Cancelled&body=Hi, your booking slot has been deleted by the owner.`
      }
      setSlots(prev => prev.filter(s => s.id !== slot_id))
    } catch(err){
      alert(err.message)
    } 
  }

  // Email the person who booked a slot, not full email
  const handleEmailBookedUser = (slot) => {
    if (!slot.booked_by_email) return
    window.location.href = `mailto:${slot.booked_by_email}?subject=Regarding your booking`
  }

  // Accept request again transform into json for react
  const handleAcceptRequest = async (request_id) => {
    try{ //WILL HAVE TO CHANGE NAME WHEN PHP FINISH
      await apiPost("accept_request.php", {request_id})
    
      setRequests(prev =>
        prev.map(r => r.id === request_id ? { ...r, status: 'accepted' } : r)
      )
    } catch(err) {
      alert(err.message)
    }
  }

  // Decline request 
  const handleDeclineRequest = async (request_id) => {
    try { //WILL HAVE TO CHANGE NAME WHEN PHP FINISH
      await apiPost("decline_request.php", {request_id})
      setRequests(prev =>
        prev.map(r => r.id === request_id ? { ...r, status: 'declined' } : r)
      )
    } catch(err){
      alert(err.message)
    }
  }

  // _______________________Create single slot transforming to php -> json -> react
  const handleCreateSlot = async () => {
    setSlotError('')
    setSlotSuccess('')
    setSlotLoading(true)

    try{
      const data = await apiPost("create_slot.php", {
        slot_date: slotDate,
        start_time: startTime,
        end_time: endTime
      })
      setSlotSuccess(data.message)
      setSlotDate('')
      setStartTime('')
      setEndTime('')
      // Refresh slots list
      apiGet("owner_slots.php").then( d=> setSlots(d.slots || []))
    }catch(err) {
      setSlotError(err.message)
    } finally{
    setSlotLoading(false)}
  }
  // ____________________________ create recurrign OFFICE HOURS
 const handleCreateRecurring = async () => {
    setRecurError(''); setRecurSuccess(''); setRecurLoading(true)
    try {
      const data = await apiPost("create_recurring_office_hours.php", {
        weekday: recurWeekday,
        start_time: recurStart,
        end_time: recurEnd,
        weeks: recurWeeks
      })
      setRecurSuccess(data.message)
      setRecurWeekday(''); setRecurStart(''); setRecurEnd(''); setRecurWeeks('')
      apiGet("owner_slots.php").then(d => setSlots(d.slots || []))
    } catch (err) { setRecurError(err.message) }
    finally { setRecurLoading(false) }
  }


  // __________________Group meeting option helpers transforming to php -> json -> react
  const updateMeetingOption = (index, field, value) => {
    setMeetingOptions(prev =>
      prev.map((opt, i) => i === index ? { ...opt, [field]: value } : opt)
    )
  }

  const addMeetingOption = () => {
    setMeetingOptions(prev => [...prev, { option_date: '', start_time: '', end_time: '' }])
  }

  const removeMeetingOption = (index) => {
    setMeetingOptions(prev => prev.filter((_, i) => i !== index))
  }

  // Create group meeting transforming to php -> json -> react
  const handleCreateGroupMeeting = async () => {
    setMeetingError('')
    setMeetingSuccess('')
    setMeetingLoading(true)

    try{
        const data = await apiPost("create_group_meeting.php", {
          title: meetingTitle,
          description: meetingDescription,
          options: meetingOptions
        })
        setMeetingSuccess(data.message)
        setMeetingTitle('')
        setMeetingDescription('')
        setMeetingOptions([
          { option_date: '', start_time: '', end_time: '' },
          { option_date: '', start_time: '', end_time: '' },
        ])
      } catch(err) {
        setMeetingError(err.message)
      } finally {
        setMeetingLoading(false)
      }
  }
    // *******************_______View votes for a group meeting ────────────────────────────────
  const handleViewVotes = async (meeting) => {
    setSelectedMeeting(meeting)
    setMeetingVotes([])
    setFinalizeMessage('')
    setVotesLoading(true)
    setView("view_votes")
    try {
      const data = await apiGet(`view_group_counts.php?group_meeting_id=${meeting.id}`)
      setMeetingVotes(data.options || [])
    } catch (err) { setFinalizeMessage(err.message) }
    finally { setVotesLoading(false) }
  }
 
  // ***************__________________ Finalize a group meeting option ───────────────────────────────
  const handleFinalizeOption = async (option_id) => {
    if (!window.confirm('Finalize this time slot? It will be created as an active booking slot.')) return
    setFinalizeMessage('')
    try {
      const data = await apiPost("finalize_group_meeting.php", { option_id })
      setFinalizeMessage(data.message)
      apiGet("owner_slots.php").then(d => setSlots(d.slots || []))
    } catch (err) { setFinalizeMessage(err.message) }
  }

  //  ####################################%%%%%%%%%%%%%%%%%%%_____________________ actual UI 
  return (
    <div>
      <Navbar onLogout={onLogout} user={user} />
      <div className="dashboard">
        <h1>Welcome back, {user.name}!</h1>

        {/* ___________ FOR INVITATION ___________ */}
        <div className="invite-banner">
          <span>Your booking link:</span>
          <code>{inviteUrl}</code>
          <button onClick={handleCopyInvite}>{inviteCopied ? "Copied!" : "Copy Link"}</button>
        </div>

        {/* TABS */}
        <div className="dashboard-tabs">
          <button className="appt-tab-btn" onClick={() => setView("slots")}>
            My Slots
          </button>

          <button className="appt-tab-btn" onClick={() => { setView("requests"); fetchRequests() }}>
            Meeting Requests
          </button>

          <button className="appt-tab-btn" onClick={() => setView("create_slot")}>
            Create Slot
          </button>

          <button className="appt-tab-btn" onClick={() => setView("create_group")}>
            Create Group Meeting
          </button>

          <button onClick={() => setView("group_meetings")}>
            View Group Votes
          </button>
        </div>

        {/* == MY SLOTS == */}
        {view === "slots" && (
          <section className="appointments-view">
            <h2>My Slots</h2>
            {slotsLoading ? (
              <p>Loading...</p>
            ) : slots.length === 0 ? (
              <p>You have no slots yet.</p>
            ) : (
              <table className="dashboard-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <th>Booked By</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {slots.map(slot => (
                    <tr key={slot.id}>
                      <td>{slot.slot_date}</td>
                      <td>{slot.start_time}</td>
                      <td>{slot.end_time}</td>
                      <td>{slot.is_active ? "Active" : "Private"}</td>
                      <td>{slot.booked_by ?? "Not booked"}</td>
                      <td>
                        <button onClick={() => handleToggleVisibility(slot.id, slot.is_active)}>
                          {slot.is_active ? "Make Private" : "Make Active"}
                        </button>
                         {slot.booked_by && (
                          <button onClick={() => handleEmailBookedUser(slot)}>Email User</button>
                        )}
                        <button onClick={() => handleDeleteSlot(slot.id)}>
                          Delete
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </section>
        )}

        {/* ── MEETING REQUESTS ── */}
        {view === "requests" && (
          <section className="browse-view">
            <h2>Meeting Requests</h2>
            {!requestsLoaded ? (
              <p>Loading...</p>
            ) : requests.length === 0 ? (
              <p>No meeting requests.</p>
            ) : (
              <table className="dashboard-table">
                <thead>
                  <tr>
                    <th>From</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {requests.map(req => (
                    <tr key={req.id}>
                      <td>{req.requester_name}</td>
                      <td>
                        <a href={`mailto:${req.requester_email}`}>{req.requester_email}</a>
                      </td>
                      <td>{req.message}</td>
                      <td>{req.status}</td>
                      <td>{req.created_at}</td>
                      <td>
                        {req.status === "pending" && (
                          <>
                            <button onClick={() => handleAcceptRequest(req.id)}>Accept</button>
                            <button onClick={() => handleDeclineRequest(req.id)}>Decline</button>
                          </>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </section>
        )}

        {/* ── CREATE SLOT ── */}
        {view === "create_slot" && (
          <div className="auth-page">
          <section className="auth-card">
            <h2>Create a Slot</h2>

            {slotError && <p className="auth-error">{slotError}</p>}
            {slotSuccess && <p className="auth-success">{slotSuccess}</p>}

            <div className="auth-form">
              <div className="form-group">
                <label>Date</label>
                <input type="date" value={slotDate} onChange={e => setSlotDate(e.target.value)} />
              </div>
              <div className="form-group">
                <label>Start Time</label>
                <input type="time" value={startTime} onChange={e => setStartTime(e.target.value)} />
              </div>
              <div className="form-group">
                <label>End Time</label>
                <input type="time" value={endTime} onChange={e => setEndTime(e.target.value)} />
              </div>

              <button className="btn-primary btn-full" onClick={handleCreateSlot} disabled={slotLoading}>
                {slotLoading ? 'Creating...' : 'Create Slot'}
              </button>
            </div>
          </section>
          </div>
        )}

        {/* **********************______________________RECCURING OFFICE HOURS _________________******************* */}
        {view === "recurring" && (
        <div className="auth-page">
          <section className="auth-card">
            <h2>Recurring Office Hours</h2>

            <p className="auth-subtitle">Create the same slot every week for X weeks</p>
            {recurError && <p className="auth-error">{recurError}</p>}
            {recurSuccess && <p className="auth-success">{recurSuccess}</p>}
            <div className="auth-form">
              <div className="form-group">
                <label>Weekday</label>
                <select value={recurWeekday} onChange={e => setRecurWeekday(e.target.value)}>
                  <option value="">-- Select a weekday --</option>
                  {["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"].map(d => (
                    <option key={d} value={d}>{d}</option>
                  ))}
                </select>
              </div>
              <div className="form-group">
                <label>Start Time</label>
                <input type="time" value={recurStart} onChange={e => setRecurStart(e.target.value)} />
              </div>
              <div className="form-group">
                <label>End Time</label>
                <input type="time" value={recurEnd} onChange={e => setRecurEnd(e.target.value)} />
              </div>
              <div className="form-group">
                <label>Number of Weeks</label>
                <input type="number" min="1" value={recurWeeks} onChange={e => setRecurWeeks(e.target.value)} />
              </div>
              <button className="btn-primary btn-full" onClick={handleCreateRecurring} disabled={recurLoading}>
                {recurLoading ? 'Creating...' : 'Create Recurring Slots'}
              </button>
            </div>
          </section>
        </div>
        )}

        {/* ################## CREATE GROUP MEETING ################### */}
        {view === "create_group" && (
          <div className='auth-page'>
          <section className="auth-card">
            <h2>Create Group Meeting</h2>

            {meetingError && <p className="auth-error">{meetingError}</p>}
            {meetingSuccess && <p className="auth-success">{meetingSuccess}</p>}

            <div className="auth-form">
              <div className="form-group">
                <label>Meeting Title</label>
                <input
                  type="text"
                  placeholder="e.g. Office Hours Week 3"
                  value={meetingTitle}
                  onChange={e => setMeetingTitle(e.target.value)}
                />
              </div>
              <div className="form-group">
                <label>Description (optional)</label>
                <textarea
                  placeholder="Any details for attendees..."
                  value={meetingDescription}
                  onChange={e => setMeetingDescription(e.target.value)}
                  rows={3}
                />
              </div>

              <h3>Meeting Options</h3> {/* WILL HAVE TO REVAMP THIS, BASIC UI BUT UGLY */}
              {meetingOptions.map((opt, i) => (
                <div key={i} className="form-group meeting-option-row">
                  <label>Option {i + 1}</label>
                  <input type="date" value={opt.option_date} onChange={e => updateMeetingOption(i, 'option_date', e.target.value)} />
                  <input type="time" value={opt.start_time}  onChange={e => updateMeetingOption(i, 'start_time',  e.target.value)} />
                  <input type="time" value={opt.end_time}    onChange={e => updateMeetingOption(i, 'end_time',    e.target.value)} />
                  {meetingOptions.length > 1 && (
                    <button onClick={() => removeMeetingOption(i)}>Remove</button>
                  )}
                </div>
              ))}

              <button onClick={addMeetingOption}>+ Add Option</button>

              <button
                className="btn-primary btn-full"
                onClick={handleCreateGroupMeeting}
                disabled={meetingLoading}
              >
                {meetingLoading ? 'Creating...' : 'Create Group Meeting'}
              </button>
            </div>
          </section>
          </div>
        )}

        {/******************______________VIEW NUMBER OF VOTES GROUP MEETING_____________********************* */}
        {view === "group_meetings" && (
          <section className="appointments-view">
            <h2>My Group Meetings</h2>
            {groupMeetings.length === 0 ? (
              <p>No group meetings yet. Create one first.</p>
            ) : (
              <div className="owners-list">
                {groupMeetings.map(meeting => (
                  <div key={meeting.id} className="owner-card">
                    <strong>{meeting.title}</strong>
                    <button className="btn-primary" onClick={() => handleViewVotes(meeting)}>
                      View Votes
                    </button>
                  </div>
                ))}
              </div>
            )}
          </section>
        )}
 
        {/* **************_____________VOTE COUNTS + FINALIZE________________***************** */}
        {view === "view_votes" && selectedMeeting && (
          <section className="appointments-view">
            <button onClick={() => setView("group_meetings")}>← Back</button>
            <h2>Votes for: {selectedMeeting.title}</h2>
            {finalizeMessage && <p className="form-message">{finalizeMessage}</p>}
            {votesLoading ? <p>Loading votes...</p> : meetingVotes.length === 0 ? (
              <p>No votes yet.</p>
            ) : (
              <table className="dashboard-table">
                <thead>
                  <tr>
                    <th>Date</th><th>Start</th><th>End</th>
                    <th>Votes</th><th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  {meetingVotes.map(option => (
                    <tr key={option.id}>
                      <td>{option.option_date}</td>
                      <td>{option.start_time}</td>
                      <td>{option.end_time}</td>
                      <td>{option.vote_count}</td>
                      <td>
                        <button className="btn-primary" onClick={() => handleFinalizeOption(option.id)}>
                          Finalize
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </section>
        )}



        {/* BOTTOM ACTIONS  $$$$$$$$$$$$$$$$$$$$$$$$$$ maybe put this button elsewhere $$$$$$$$$$$$$$$$$$$$$$$$$$$$ */}
        <div className="dashboard-actions">
          <button onClick={onLogout}>Logout</button>
        </div>

      </div>
    </div>
  )
}

export default OwnerDashboard