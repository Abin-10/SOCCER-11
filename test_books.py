# Generated by Selenium IDE
import pytest
import time
import json
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.wait import WebDriverWait
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.common.exceptions import NoSuchElementException, UnexpectedAlertPresentException, TimeoutException

class TestBooks():
  def setup_method(self, method):
    self.driver = webdriver.Chrome()
    self.vars = {}
    self.wait = WebDriverWait(self.driver, 10)  # 10 seconds timeout
  
  def teardown_method(self, method):
    self.driver.quit()
  
  def wait_for_window(self, timeout = 2):
    time.sleep(round(timeout / 1000))
    wh_now = self.driver.window_handles
    wh_then = self.vars["window_handles"]
    if len(wh_now) > len(wh_then):
      return set(wh_now).difference(set(wh_then)).pop()
  
  def test_books(self):
    # Navigate to the home page
    self.driver.get("http://localhost/Exam/index.html")
    self.driver.set_window_size(1552, 832)
    
    # Click on login button
    self.driver.find_element(By.CSS_SELECTOR, ".logbtn").click()
    
    # Wait for login form to be visible
    self.wait.until(EC.visibility_of_element_located((By.CSS_SELECTOR, "#signinForm")))
    
    # Fill in login credentials
    self.driver.find_element(By.CSS_SELECTOR, "#signinForm input[type='email']").click()
    self.driver.find_element(By.CSS_SELECTOR, "#signinForm input[type='email']").send_keys("abin10108@gmail.com")
    self.driver.find_element(By.CSS_SELECTOR, "#signinForm input[type='password']").click()
    self.driver.find_element(By.CSS_SELECTOR, "#signinForm input[type='password']").send_keys("User1234")
    
    # Submit login form
    self.driver.find_element(By.CSS_SELECTOR, "#signinForm button[type='submit']").click()
    
    # Wait for login to complete and redirect to dashboard
    time.sleep(3)  # Give time for redirect
    
    # Print current URL for debugging
    print(f"Current URL after login: {self.driver.current_url}")
    
    # Try to navigate to the booking page directly
    try:
        # First try to find and click the "Book Turf" link if it exists
        try:
            book_turf_link = self.wait.until(EC.element_to_be_clickable((By.LINK_TEXT, "Book Turf")))
            book_turf_link.click()
        except TimeoutException:
            # If "Book Turf" link not found, try to navigate directly to the booking page
            print("'Book Turf' link not found, navigating directly to booking page")
            self.driver.get("http://localhost/Exam/book_turf.php")
        
        # Wait for the booking page to load
        time.sleep(2)
        
        # Handle the booking form
        try:
            # First, get all available turf options
            turf_select = self.wait.until(EC.presence_of_element_located((By.ID, "turf-select")))
            turf_options = turf_select.find_elements(By.TAG_NAME, "option")
            
            # Skip the first option (usually "Choose a turf")
            if len(turf_options) > 1:
                # Select the first available turf
                turf_options[1].click()
                print(f"Selected turf: {turf_options[1].text}")
            else:
                print("No turf options available")
                return
            
            # Set the date
            date_input = self.driver.find_element(By.ID, "date")
            date_input.clear()
            date_input.send_keys("2025-04-09")
            
            # Wait for time slots to load
            time.sleep(2)
            
            # Select a time slot
            time_slot_select = self.driver.find_element(By.ID, "time-slot")
            time_slot_options = time_slot_select.find_elements(By.TAG_NAME, "option")
            
            if len(time_slot_options) > 1:
                # Select the first available time slot
                time_slot_options[1].click()
                print(f"Selected time slot: {time_slot_options[1].text}")
            else:
                print("No time slots available")
                return
            
            # Click the pay button
            pay_button = self.driver.find_element(By.ID, "pay-button")
            pay_button.click()
            
            # Handle Razorpay iframe
            time.sleep(2)
            self.driver.switch_to.frame(0)
            self.driver.find_element(By.CSS_SELECTOR, ".rounded-tr-lg > .relative").click()
            
            # Handle success window
            self.vars["window_handles"] = self.driver.window_handles
            self.driver.find_element(By.CSS_SELECTOR, ".relative:nth-child(1) > .flex > .flex > .flex > .mx-4 .mt-0\\.5").click()
            self.vars["win1693"] = self.wait_for_window(2000)
            self.vars["root"] = self.driver.current_window_handle
            self.driver.switch_to.window(self.vars["win1693"])
            self.driver.find_element(By.CSS_SELECTOR, ".success").click()
            self.driver.close()
            self.driver.switch_to.window(self.vars["root"])
            self.driver.find_element(By.CSS_SELECTOR, ".btn:nth-child(1)").click()
            
        except UnexpectedAlertPresentException as e:
            # Handle the alert
            alert = self.driver.switch_to.alert
            print(f"Alert text: {alert.text}")
            alert.accept()
            print("Alert accepted")
        except Exception as e:
            print(f"Error during booking: {str(e)}")
            # Take a screenshot for debugging
            self.driver.save_screenshot("error_screenshot.png")
            raise
            
    except Exception as e:
        print(f"Error navigating to booking page: {str(e)}")
        # Take a screenshot for debugging
        self.driver.save_screenshot("navigation_error.png")
        raise
  
