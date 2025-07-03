import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

function Board() {
  const [messages, setMessages] = useState([]);
  const [content, setContent] = useState('');
  const [image, setImage] = useState(null);
  const [user, setUser] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    const loggedInUser = localStorage.getItem('user');
    if (loggedInUser) {
      setUser(JSON.parse(loggedInUser));
      fetchMessages();
    } else {
      navigate('/login');
    }
  }, [navigate]);

  const fetchMessages = async () => {
    try {
      const response = await fetch(`${process.env.REACT_APP_API_BASE_URL}/messages`);
      const data = await response.json();
      setMessages(data);
    } catch (error) {
      console.error('Error fetching messages:', error);
    }
  };

  const handleImageChange = (e) => {
    setImage(e.target.files[0]);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData();
    formData.append('content', content);
    if (image) {
      formData.append('image', image);
    }

    try {
      const response = await fetch(`${process.env.REACT_APP_API_BASE_URL}/messages`, {
        method: 'POST',
        body: formData,
        credentials: 'include',
      });
      if (response.ok) {
        setContent('');
        setImage(null);
        fetchMessages();
      } else {
        console.error('Error posting message');
      }
    } catch (error) {
      console.error('Error posting message:', error);
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('user');
    navigate('/login');
  };

  return (
    <div className="App">
      <header className="App-header">
        <h1>Bulletin Board</h1>
        {user && <span>Welcome, {user.username}!</span>}
        <button onClick={handleLogout}>Logout</button>
      </header>
      <main>
        <div className="message-form">
          <form onSubmit={handleSubmit}>
            <textarea
              placeholder="Message"
              value={content}
              onChange={(e) => setContent(e.target.value)}
              required
            ></textarea>
            <input type="file" onChange={handleImageChange} />
            <button type="submit">Post Message</button>
          </form>
        </div>
        <div className="message-list">
          {messages.map((message) => (
            <div key={message.id} className="message">
              <div className="message-header">
                <strong>{message.username}</strong> -{' '}
                <em>{new Date(message.created_at).toLocaleString()}</em>
              </div>
              <p>{message.content}</p>
              {message.image_path && (
                <img src={`${process.env.REACT_APP_API_BASE_URL}/${message.image_path}`} alt="message attachment" />
              )}
            </div>
          ))}
        </div>
      </main>
    </div>
  );
}

export default Board;
