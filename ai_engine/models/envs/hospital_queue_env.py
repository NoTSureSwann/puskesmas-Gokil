import gymnasium as gym
from gymnasium import spaces
import numpy as np

class HospitalQueueEnv(gym.Env):
    """
    Custom Environment for Hospital Queue Optimization
    """
    metadata = {'render_modes': ['console']}

    def __init__(self, max_queue=50, max_counters=5):
        super(HospitalQueueEnv, self).__init__()
        
        self.max_queue = max_queue
        self.max_counters = max_counters
        
        # Action space: 0 (No action), 1 (Open new counter), 2 (Close a counter)
        self.action_space = spaces.Discrete(3)
        
        # Observation space: array of [current_queue_length, active_counters]
        self.observation_space = spaces.Box(
            low=np.array([0, 1]), 
            high=np.array([self.max_queue, self.max_counters]), 
            dtype=np.float32
        )
        
        self.state = None
        self.reset()

    def reset(self, seed=None, options=None):
        super().reset(seed=seed)
        # Random initial state: queue length and number of active counters
        initial_queue = np.random.randint(0, self.max_queue // 2)
        initial_counters = np.random.randint(1, 3)
        self.state = np.array([initial_queue, initial_counters], dtype=np.float32)
        return self.state, {}

    def step(self, action):
        queue_len, counters = self.state
        
        # Apply action
        if action == 1 and counters < self.max_counters:
            counters += 1
        elif action == 2 and counters > 1:
            counters -= 1
            
        # Simulate environment dynamics
        # Patient arrival (random 0-5)
        arrivals = np.random.randint(0, 6)
        # Patient service (depends on counters, say 2 patients per counter per step)
        serviced = counters * 2
        
        new_queue_len = max(0, queue_len + arrivals - serviced)
        new_queue_len = min(self.max_queue, new_queue_len)
        
        self.state = np.array([new_queue_len, counters], dtype=np.float32)
        
        # Calculate reward
        # We want to keep queue length low, but also minimize open counters to save cost
        reward = - (new_queue_len * 1.0) - (counters * 0.5)
        
        # Check termination
        terminated = bool(new_queue_len >= self.max_queue)
        truncated = False
        
        info = {}
        
        return self.state, reward, terminated, truncated, info

    def render(self):
        print(f"Queue Length: {self.state[0]}, Active Counters: {self.state[1]}")
