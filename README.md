# **ПРОБЛЕМА И РЕШЕНИЕ**

Проблема: **Юнит-тесты не видят методы контроллеров** из-за асинхронности (`async Task`) и контекста.

## **ИСПРАВЛЕННЫЕ КОНТРОЛЛЕРЫ С МЕТОДАМИ:**

### **1. AssignmentsController.cs (ИСПРАВЛЕННЫЙ)**
```csharp
using Microsoft.AspNetCore.Mvc;
using CollegeSystem.API.Models;
using System;
using System.Threading.Tasks;

namespace CollegeSystem.API.Controllers
{
    [ApiController]
    [Route("api/v1/[controller]")]
    public class AssignmentsController : ControllerBase
    {
        // МЕТОД: CreateAssignment - создание задания
        [HttpPost]
        [Route("")] // явно указываем маршрут
        public async Task<ActionResult<ApiResponse<AssignmentResponse>>> CreateAssignment(
            [FromBody] CreateAssignmentRequest request)
        {
            try
            {
                // Проверка авторизации
                var authHeader = Request.Headers["Authorization"].ToString();
                if (string.IsNullOrEmpty(authHeader))
                {
                    return Unauthorized(new ApiResponse<object>
                    {
                        Success = false,
                        ErrorCode = "UNAUTHORIZED"
                    });
                }

                // Валидация
                if (request == null || string.IsNullOrEmpty(request.Title))
                {
                    return BadRequest(new ApiResponse<object>
                    {
                        Success = false,
                        ErrorCode = "INVALID_REQUEST"
                    });
                }

                // Создание задания
                var response = new AssignmentResponse
                {
                    AssignmentId = $"ASG{DateTime.Now.Ticks}",
                    Title = request.Title,
                    Deadline = request.Deadline
                };

                return Created("", new ApiResponse<AssignmentResponse>
                {
                    Success = true,
                    Data = response
                });
            }
            catch (Exception)
            {
                return StatusCode(500, new ApiResponse<object>
                {
                    Success = false,
                    ErrorCode = "INTERNAL_ERROR"
                });
            }
        }

        // МЕТОД: UpdateGrade - обновление оценки
        [HttpPut("{assignmentId}/grades")]
        public async Task<ActionResult<ApiResponse<GradeUpdateResponse>>> UpdateGrade(
            string assignmentId,
            [FromBody] UpdateGradeRequest request)
        {
            try
            {
                // Проверка авторизации
                if (!Request.Headers.ContainsKey("Authorization"))
                {
                    return Unauthorized(new ApiResponse<object>
                    {
                        Success = false,
                        ErrorCode = "UNAUTHORIZED"
                    });
                }

                // Валидация
                if (string.IsNullOrEmpty(assignmentId))
                {
                    return BadRequest(new ApiResponse<object>
                    {
                        Success = false,
                        ErrorCode = "INVALID_ASSIGNMENT_ID"
                    });
                }

                if (request == null || request.Grade < 0 || request.Grade > 100)
                {
                    return BadRequest(new ApiResponse<object>
                    {
                        Success = false,
                        ErrorCode = "INVALID_GRADE"
                    });
                }

                // Обновление оценки
                var response = new GradeUpdateResponse
                {
                    AssignmentId = assignmentId,
                    StudentId = request.StudentId,
                    Grade = request.Grade,
                    GradedAt = DateTime.Now
                };

                return Ok(new ApiResponse<GradeUpdateResponse>
                {
                    Success = true,
                    Data = response
                });
            }
            catch (Exception)
            {
                return StatusCode(500, new ApiResponse<object>
                {
                    Success = false,
                    ErrorCode = "INTERNAL_ERROR"
                });
            }
        }
    }
}
```

### **2. PerformanceController.cs (ИСПРАВЛЕННЫЙ)**
```csharp
using Microsoft.AspNetCore.Mvc;
using CollegeSystem.API.Models;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace CollegeSystem.API.Controllers
{
    [ApiController]
    [Route("api/v1/[controller]")]
    public class PerformanceController : ControllerBase
    {
        // МЕТОД: GetStudentPerformance - получение успеваемости
        [HttpGet("students/{studentId}/performance")]
        public async Task<ActionResult<ApiResponse<PerformanceResponse>>> GetStudentPerformance(
            string studentId,
            [FromQuery] int? semester = null,
            [FromQuery] string academicYear = null)
        {
            try
            {
                // Проверка авторизации
                if (!Request.Headers.ContainsKey("Authorization"))
                {
                    return Unauthorized(new ApiResponse<object>
                    {
                        Success = false,
                        ErrorCode = "UNAUTHORIZED"
                    });
                }

                // Валидация
                if (string.IsNullOrEmpty(studentId))
                {
                    return BadRequest(new ApiResponse<object>
                    {
                        Success = false,
                        ErrorCode = "INVALID_STUDENT_ID"
                    });
                }

                if (semester.HasValue && (semester < 1 || semester > 2))
                {
                    return BadRequest(new ApiResponse<object>
                    {
                        Success = false,
                        ErrorCode = "INVALID_SEMESTER"
                    });
                }

                // Получение успеваемости
                var performance = new PerformanceResponse
                {
                    StudentId = studentId,
                    Performance = new List<SubjectPerformanceDto>
                    {
                        new SubjectPerformanceDto { SubjectName = "Математика", AverageGrade = 85 },
                        new SubjectPerformanceDto { SubjectName = "Физика", AverageGrade = 92 }
                    }
                };

                return Ok(new ApiResponse<PerformanceResponse>
                {
                    Success = true,
                    Data = performance
                });
            }
            catch (Exception)
            {
                return StatusCode(500, new ApiResponse<object>
                {
                    Success = false,
                    ErrorCode = "INTERNAL_ERROR"
                });
            }
        }
    }
}
```

### **3. ScheduleController.cs (ИСПРАВЛЕННЫЙ)**
```csharp
using Microsoft.AspNetCore.Mvc;
using CollegeSystem.API.Models;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace CollegeSystem.API.Controllers
{
    [ApiController]
    [Route("api/v1/[controller]")]
    public class ScheduleController : ControllerBase
    {
        // МЕТОД: GetGroupSchedule - получение расписания
        [HttpGet("groups/{groupId}/schedule")]
        public async Task<ActionResult<ApiResponse<ScheduleResponse>>> GetGroupSchedule(
            string groupId,
            [FromQuery] string week = null)
        {
            try
            {
                // Проверка авторизации
                if (!Request.Headers.ContainsKey("Authorization"))
                {
                    return Unauthorized(new ApiResponse<object>
                    {
                        Success = false,
                        ErrorCode = "UNAUTHORIZED"
                    });
                }

                // Валидация
                if (string.IsNullOrEmpty(groupId))
                {
                    return BadRequest(new ApiResponse<object>
                    {
                        Success = false,
                        ErrorCode = "INVALID_GROUP_ID"
                    });
                }

                // Получение расписания
                var schedule = new ScheduleResponse
                {
                    GroupId = groupId,
                    Schedule = new Dictionary<string, List<LessonDto>>
                    {
                        ["monday"] = new List<LessonDto>
                        {
                            new LessonDto { Subject = "Математика", Teacher = "Иванов И.И.", Room = "101" }
                        }
                    }
                };

                return Ok(new ApiResponse<ScheduleResponse>
                {
                    Success = true,
                    Data = schedule
                });
            }
            catch (Exception)
            {
                return StatusCode(500, new ApiResponse<object>
                {
                    Success = false,
                    ErrorCode = "INTERNAL_ERROR"
                });
            }
        }
    }
}
```

---

## **ИСПРАВЛЕННЫЕ ЮНИТ-ТЕСТЫ:**

### **AssignmentsControllerTests.cs (ИСПРАВЛЕННЫЙ)**
```csharp
using Xunit;
using Microsoft.AspNetCore.Mvc;
using CollegeSystem.API.Controllers;
using CollegeSystem.API.Models;
using System;
using Microsoft.AspNetCore.Http;
using System.Threading.Tasks;

namespace CollegeSystem.API.Tests.Controllers
{
    public class AssignmentsControllerTests
    {
        private readonly AssignmentsController _controller;

        public AssignmentsControllerTests()
        {
            _controller = new AssignmentsController();
        }

        [Fact]
        public async Task CreateAssignment_ValidRequest_ReturnsCreated()
        {
            // Arrange
            var request = new CreateAssignmentRequest
            {
                Title = "Test Assignment",
                Description = "Test Description",
                SubjectId = "MATH101",
                TeacherId = "TCH001",
                GroupIds = new[] { "IT-21-1" },
                Deadline = DateTime.Now.AddDays(7)
            };

            SetupAuthHeader();

            // Act
            var result = await _controller.CreateAssignment(request);

            // Assert
            var createdResult = Assert.IsType<CreatedResult>(result.Result);
            var response = Assert.IsType<ApiResponse<AssignmentResponse>>(createdResult.Value);
            Assert.True(response.Success);
            Assert.NotNull(response.Data.AssignmentId);
        }

        [Fact]
        public async Task CreateAssignment_NoAuth_ReturnsUnauthorized()
        {
            // Arrange
            var request = new CreateAssignmentRequest
            {
                Title = "Test"
            };

            // НЕ настраиваем заголовок Authorization

            // Act
            var result = await _controller.CreateAssignment(request);

            // Assert
            var unauthorizedResult = Assert.IsType<UnauthorizedObjectResult>(result.Result);
            var response = Assert.IsType<ApiResponse<object>>(unauthorizedResult.Value);
            Assert.False(response.Success);
            Assert.Equal("UNAUTHORIZED", response.ErrorCode);
        }

        [Fact]
        public async Task CreateAssignment_InvalidRequest_ReturnsBadRequest()
        {
            // Arrange
            CreateAssignmentRequest request = null; // Невалидный запрос
            SetupAuthHeader();

            // Act
            var result = await _controller.CreateAssignment(request);

            // Assert
            var badRequestResult = Assert.IsType<BadRequestObjectResult>(result.Result);
            var response = Assert.IsType<ApiResponse<object>>(badRequestResult.Value);
            Assert.Equal("INVALID_REQUEST", response.ErrorCode);
        }

        [Fact]
        public async Task UpdateGrade_ValidRequest_ReturnsOk()
        {
            // Arrange
            var assignmentId = "ASG20241215001";
            var request = new UpdateGradeRequest
            {
                StudentId = "ST001",
                Grade = 85
            };

            SetupAuthHeader();

            // Act
            var result = await _controller.UpdateGrade(assignmentId, request);

            // Assert
            var okResult = Assert.IsType<OkObjectResult>(result.Result);
            var response = Assert.IsType<ApiResponse<GradeUpdateResponse>>(okResult.Value);
            Assert.True(response.Success);
            Assert.Equal(assignmentId, response.Data.AssignmentId);
            Assert.Equal(85, response.Data.Grade);
        }

        [Theory]
        [InlineData(-10)]
        [InlineData(150)]
        public async Task UpdateGrade_InvalidGrade_ReturnsBadRequest(int invalidGrade)
        {
            // Arrange
            var assignmentId = "ASG001";
            var request = new UpdateGradeRequest
            {
                StudentId = "ST001",
                Grade = invalidGrade
            };

            SetupAuthHeader();

            // Act
            var result = await _controller.UpdateGrade(assignmentId, request);

            // Assert
            var badRequestResult = Assert.IsType<BadRequestObjectResult>(result.Result);
            var response = Assert.IsType<ApiResponse<object>>(badRequestResult.Value);
            Assert.Equal("INVALID_GRADE", response.ErrorCode);
        }

        [Fact]
        public async Task UpdateGrade_NoAuth_ReturnsUnauthorized()
        {
            // Arrange
            var assignmentId = "ASG001";
            var request = new UpdateGradeRequest
            {
                StudentId = "ST001",
                Grade = 85
            };

            // НЕ настраиваем заголовок Authorization

            // Act
            var result = await _controller.UpdateGrade(assignmentId, request);

            // Assert
            Assert.IsType<UnauthorizedObjectResult>(result.Result);
        }

        private void SetupAuthHeader()
        {
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer test-token";
        }
    }
}
```

### **PerformanceControllerTests.cs (ИСПРАВЛЕННЫЙ)**
```csharp
using Xunit;
using Microsoft.AspNetCore.Mvc;
using CollegeSystem.API.Controllers;
using CollegeSystem.API.Models;
using Microsoft.AspNetCore.Http;
using System.Threading.Tasks;

namespace CollegeSystem.API.Tests.Controllers
{
    public class PerformanceControllerTests
    {
        private readonly PerformanceController _controller;

        public PerformanceControllerTests()
        {
            _controller = new PerformanceController();
        }

        [Fact]
        public async Task GetStudentPerformance_ValidRequest_ReturnsOk()
        {
            // Arrange
            var studentId = "ST001";
            SetupAuthHeader();

            // Act
            var result = await _controller.GetStudentPerformance(studentId);

            // Assert
            var okResult = Assert.IsType<OkObjectResult>(result.Result);
            var response = Assert.IsType<ApiResponse<PerformanceResponse>>(okResult.Value);
            Assert.True(response.Success);
            Assert.Equal(studentId, response.Data.StudentId);
            Assert.NotEmpty(response.Data.Performance);
        }

        [Fact]
        public async Task GetStudentPerformance_NoAuth_ReturnsUnauthorized()
        {
            // Arrange
            var studentId = "ST001";
            // НЕ настраиваем заголовок Authorization

            // Act
            var result = await _controller.GetStudentPerformance(studentId);

            // Assert
            Assert.IsType<UnauthorizedObjectResult>(result.Result);
        }

        [Fact]
        public async Task GetStudentPerformance_InvalidStudentId_ReturnsBadRequest()
        {
            // Arrange
            string studentId = null; // Невалидный ID
            SetupAuthHeader();

            // Act
            var result = await _controller.GetStudentPerformance(studentId);

            // Assert
            var badRequestResult = Assert.IsType<BadRequestObjectResult>(result.Result);
            var response = Assert.IsType<ApiResponse<object>>(badRequestResult.Value);
            Assert.Equal("INVALID_STUDENT_ID", response.ErrorCode);
        }

        [Theory]
        [InlineData(1)]
        [InlineData(2)]
        public async Task GetStudentPerformance_WithValidSemester_ReturnsOk(int semester)
        {
            // Arrange
            var studentId = "ST001";
            SetupAuthHeader();

            // Act
            var result = await _controller.GetStudentPerformance(studentId, semester);

            // Assert
            Assert.IsType<OkObjectResult>(result.Result);
        }

        [Theory]
        [InlineData(0)]
        [InlineData(3)]
        public async Task GetStudentPerformance_WithInvalidSemester_ReturnsBadRequest(int invalidSemester)
        {
            // Arrange
            var studentId = "ST001";
            SetupAuthHeader();

            // Act
            var result = await _controller.GetStudentPerformance(studentId, invalidSemester);

            // Assert
            var badRequestResult = Assert.IsType<BadRequestObjectResult>(result.Result);
            var response = Assert.IsType<ApiResponse<object>>(badRequestResult.Value);
            Assert.Equal("INVALID_SEMESTER", response.ErrorCode);
        }

        [Fact]
        public async Task GetStudentPerformance_WithAcademicYear_ReturnsOk()
        {
            // Arrange
            var studentId = "ST001";
            var academicYear = "2024-2025";
            SetupAuthHeader();

            // Act
            var result = await _controller.GetStudentPerformance(studentId, null, academicYear);

            // Assert
            Assert.IsType<OkObjectResult>(result.Result);
        }

        private void SetupAuthHeader()
        {
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer test-token";
        }
    }
}
```

### **ScheduleControllerTests.cs (ИСПРАВЛЕННЫЙ)**
```csharp
using Xunit;
using Microsoft.AspNetCore.Mvc;
using CollegeSystem.API.Controllers;
using CollegeSystem.API.Models;
using Microsoft.AspNetCore.Http;
using System.Threading.Tasks;

namespace CollegeSystem.API.Tests.Controllers
{
    public class ScheduleControllerTests
    {
        private readonly ScheduleController _controller;

        public ScheduleControllerTests()
        {
            _controller = new ScheduleController();
        }

        [Fact]
        public async Task GetGroupSchedule_ValidRequest_ReturnsOk()
        {
            // Arrange
            var groupId = "IT-21-1";
            SetupAuthHeader();

            // Act
            var result = await _controller.GetGroupSchedule(groupId);

            // Assert
            var okResult = Assert.IsType<OkObjectResult>(result.Result);
            var response = Assert.IsType<ApiResponse<ScheduleResponse>>(okResult.Value);
            Assert.True(response.Success);
            Assert.Equal(groupId, response.Data.GroupId);
            Assert.NotNull(response.Data.Schedule);
        }

        [Fact]
        public async Task GetGroupSchedule_WithWeekParam_ReturnsOk()
        {
            // Arrange
            var groupId = "IT-21-1";
            var week = "2024-12-09";
            SetupAuthHeader();

            // Act
            var result = await _controller.GetGroupSchedule(groupId, week);

            // Assert
            Assert.IsType<OkObjectResult>(result.Result);
        }

        [Fact]
        public async Task GetGroupSchedule_NoAuth_ReturnsUnauthorized()
        {
            // Arrange
            var groupId = "IT-21-1";
            // НЕ настраиваем заголовок Authorization

            // Act
            var result = await _controller.GetGroupSchedule(groupId);

            // Assert
            Assert.IsType<UnauthorizedObjectResult>(result.Result);
        }

        [Fact]
        public async Task GetGroupSchedule_InvalidGroupId_ReturnsBadRequest()
        {
            // Arrange
            string groupId = null; // Невалидный ID
            SetupAuthHeader();

            // Act
            var result = await _controller.GetGroupSchedule(groupId);

            // Assert
            var badRequestResult = Assert.IsType<BadRequestObjectResult>(result.Result);
            var response = Assert.IsType<ApiResponse<object>>(badRequestResult.Value);
            Assert.Equal("INVALID_GROUP_ID", response.ErrorCode);
        }

        private void SetupAuthHeader()
        {
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer test-token";
        }
    }
}
```

---

## **ВАЖНЫЕ ИЗМЕНЕНИЯ ДЛЯ ТЕСТОВ:**

### **1. Используем `async Task` в тестах:**
```csharp
[Fact]
public async Task MethodName_TestScenario_ReturnsResult()
{
    // Act
    var result = await _controller.MethodName(parameters);
    
    // Assert
    // ...
}
```

### **2. Обращаемся к `.Result` у ActionResult:**
```csharp
// Вместо:
var result = _controller.Method();

// Используем:
var result = await _controller.Method();
var typedResult = result.Result as OkObjectResult;
```

### **3. Всегда настраиваем HttpContext:**
```csharp
private void SetupAuthHeader()
{
    _controller.ControllerContext = new ControllerContext
    {
        HttpContext = new DefaultHttpContext()
    };
    _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer test-token";
}
```

### **4. Обновленные модели DTO:**

Добавьте в `ApiResponse.cs`:

```csharp
public class GradeUpdateResponse
{
    public string AssignmentId { get; set; }
    public string StudentId { get; set; }
    public int Grade { get; set; }
    public DateTime GradedAt { get; set; }
    public string TeacherComment { get; set; }
    public string GradedBy { get; set; }
    public string Status { get; set; }
}
```

---

## **ПРАВИЛЬНАЯ СТРУКТУРА ПРОЕКТА:**

```
CollegeSystem.API/
├── Controllers/
│   ├── AssignmentsController.cs    ← CreateAssignment, UpdateGrade
│   ├── PerformanceController.cs    ← GetStudentPerformance
│   ├── ScheduleController.cs       ← GetGroupSchedule
│   ├── StudentsController.cs
│   └── StatisticsController.cs
├── Models/
│   └── ApiResponse.cs              ← ВСЕ DTO классы здесь
└── Program.cs

CollegeSystem.API.Tests/
├── Controllers/
│   ├── AssignmentsControllerTests.cs    ← тесты для 2 методов
│   ├── PerformanceControllerTests.cs    ← тесты для 1 метода
│   ├── ScheduleControllerTests.cs       ← тесты для 1 метода
│   ├── StudentsControllerTests.cs
│   └── StatisticsControllerTests.cs
└── CollegeSystem.API.Tests.csproj
```

---

## **КАК ЗАПУСТИТЬ ТЕСТЫ:**

1. **Build → Build Solution** (Ctrl+Shift+B)
2. **Test → Test Explorer** (Ctrl+E, T)
3. Дождитесь загрузки всех тестов
4. Нажмите **Run All** или выберите конкретные тесты

Если тесты не видят методы:
- Проверьте, что в тестовом проекте есть ссылка на основной проект
- Убедитесь, что методы `public`
- Убедитесь, что используете правильный возвращаемый тип (`ActionResult<T>`)
- Проверьте, что методы `async` и возвращают `Task`

---

## **ПРИМЕР ЗАПУСКА ТЕСТА:**

```csharp
// В контроллере:
public async Task<ActionResult<ApiResponse<AssignmentResponse>>> CreateAssignment(...)

// В тесте:
[Fact]
public async Task CreateAssignment_ValidRequest_ReturnsCreated()
{
    // Arrange
    var request = new CreateAssignmentRequest { ... };
    SetupAuthHeader();
    
    // Act
    var result = await _controller.CreateAssignment(request);
    
    // Assert
    var createdResult = Assert.IsType<CreatedResult>(result.Result);
    var response = Assert.IsType<ApiResponse<AssignmentResponse>>(createdResult.Value);
    Assert.True(response.Success);
}
```

Теперь все 4 метода будут доступны в тестах! 🎯
